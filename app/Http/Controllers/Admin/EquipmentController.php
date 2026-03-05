<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentController extends Controller
{
    /**
     * Display a listing of equipment for admin.
     */
    public function index(): Response
    {
        $equipment = Equipment::latest()->get()->map(function ($item) {
            if ($item->image && ! str_starts_with($item->image, 'http')) {
                $item->image = asset('storage/'.$item->image);
            }

            return $item;
        });

        return Inertia::render('admin/equipment/Index', [
            'equipment' => $equipment,
        ]);
    }

    /**
     * Fetch metadata (name, description, image) from a URL via Open Graph / meta tags.
     */
    public function fetchFromUrl(Request $request): JsonResponse
    {
        Gate::authorize('create', Equipment::class);

        $validated = $request->validate([
            'url' => ['required', 'url', 'max:500'],
        ]);

        $url = $validated['url'];

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])
                ->get($url);

            if (! $response->successful()) {
                return response()->json([
                    'message' => 'Die Seite konnte nicht geladen werden.',
                ], 422);
            }

            $html = $response->body();
            $data = $this->parseMetaFromHtml($html, $url);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Die Seite konnte nicht geladen werden: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Parse Open Graph and fallback meta tags from HTML.
     *
     * @return array{name: string, description: string, image_url: string|null, price: string|null}
     */
    private function parseMetaFromHtml(string $html, string $baseUrl): array
    {
        $name = '';
        $description = '';
        $imageUrl = null;
        $price = null;

        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($dom);

        $productTitle = $xpath->query("//*[@id='productTitle']")->item(0);
        if ($productTitle && trim($productTitle->textContent) !== '') {
            $name = trim($productTitle->textContent);
        }
        if ($name === '') {
            $ogTitle = $xpath->query("//meta[@property='og:title']")->item(0);
            if ($ogTitle && $ogTitle->getAttribute('content')) {
                $name = trim($ogTitle->getAttribute('content'));
            }
        }
        if ($name === '') {
            $titleNode = $xpath->query('//title')->item(0);
            if ($titleNode) {
                $name = trim($titleNode->textContent);
            }
        }
        $name = $this->cleanProductTitle($name);

        $productDesc = $xpath->query("//*[@id='productDescription']")->item(0);
        if ($productDesc && trim($productDesc->textContent) !== '') {
            $description = trim(preg_replace('/\s+/', ' ', $productDesc->textContent));
        }
        if ($description === '') {
            $ogDesc = $xpath->query("//meta[@property='og:description']")->item(0);
            if ($ogDesc && $ogDesc->getAttribute('content')) {
                $description = trim($ogDesc->getAttribute('content'));
            }
        }
        if ($description === '') {
            $metaDesc = $xpath->query("//meta[@name='description']")->item(0);
            if ($metaDesc && $metaDesc->getAttribute('content')) {
                $description = trim($metaDesc->getAttribute('content'));
            }
        }

        $ogImage = $xpath->query("//meta[@property='og:image']")->item(0);
        if ($ogImage && $ogImage->getAttribute('content')) {
            $imageUrl = trim($ogImage->getAttribute('content'));
            $imageUrl = $this->resolveAbsoluteUrl($imageUrl, $baseUrl);
        }
        if ($imageUrl === null) {
            $ogImageSecure = $xpath->query("//meta[@property='og:image:secure_url']")->item(0);
            if ($ogImageSecure && $ogImageSecure->getAttribute('content')) {
                $imageUrl = trim($ogImageSecure->getAttribute('content'));
            }
        }
        if ($imageUrl === null) {
            $imageUrl = $this->extractImageFromImgTags($xpath, $baseUrl);
        }

        $price = $this->extractPriceFromHtml($xpath);

        return [
            'name' => $name,
            'description' => $description,
            'image_url' => $imageUrl,
            'price' => $price,
        ];
    }

    /**
     * Extract price from HTML (e.g. Amazon span.a-price-whole + span.a-price-fraction).
     */
    private function extractPriceFromHtml(\DOMXPath $xpath): ?string
    {
        $whole = $xpath->query("//span[contains(concat(' ', normalize-space(@class), ' '), ' a-price-whole ')]")->item(0);
        $fraction = $xpath->query("//span[contains(concat(' ', normalize-space(@class), ' '), ' a-price-fraction ')]")->item(0);
        if ($whole === null || $fraction === null) {
            return null;
        }
        $wholeText = trim($whole->textContent ?? '');
        $fractionText = trim($fraction->textContent ?? '');
        if ($wholeText === '' && $fractionText === '') {
            return null;
        }
        $wholeClean = rtrim($wholeText, ',');
        $price = $fractionText !== '' ? $wholeClean.','.$fractionText : $wholeClean;
        $price = trim($price);
        if ($price === '' || $price === ',') {
            return null;
        }

        return $price.' €';
    }

    /**
     * Fallback: extract product image from img tags (e.g. Amazon data-old-hires or media CDN src).
     */
    private function extractImageFromImgTags(\DOMXPath $xpath, string $baseUrl): ?string
    {
        $img = $xpath->query('//img[@data-old-hires]')->item(0);
        if ($img && $img->getAttribute('data-old-hires')) {
            $url = trim($img->getAttribute('data-old-hires'));

            return $this->resolveAbsoluteUrl($url, $baseUrl);
        }

        foreach ($xpath->query('//img[@src]') as $img) {
            $src = trim($img->getAttribute('src') ?? '');
            if ($src === '') {
                continue;
            }
            if (preg_match('#m\.media-amazon\.com/images/#i', $src)
                || preg_match('#images-na\.ssl-images-amazon\.com/#i', $src)) {
                return $this->resolveAbsoluteUrl($src, $baseUrl);
            }
        }

        return null;
    }

    private function resolveAbsoluteUrl(string $url, string $baseUrl): string
    {
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        $base = parse_url($baseUrl);
        $scheme = $base['scheme'] ?? 'https';
        $host = $base['host'] ?? '';
        $path = $base['path'] ?? '/';
        if ($url === '' || $url[0] !== '/') {
            $path = rtrim(dirname($path), '/').'/'.$url;
        } else {
            $path = $url;
        }

        return $scheme.'://'.$host.$path;
    }

    /**
     * Remove shop/site names from product title (e.g. "Amazon.de:", " | eBay").
     */
    private function cleanProductTitle(string $title): string
    {
        $title = trim($title);
        if ($title === '') {
            return $title;
        }

        $patterns = [
            '#^\s*Amazon\.?(de|at|ch|co\.uk|com)?\s*[:\-–—|]\s*#iu',
            '#\s*[:\-–—|]\s*Amazon\.?(de|at|ch|co\.uk|com)?\s*$#iu',
            '#\s*\|\s*Amazon\.?(de|at|ch|co\.uk|com)?\s*$#iu',
            '#^\s*eBay\s*[:\-–—|]\s*#iu',
            '#\s*[:\-–—|]\s*eBay\s*$#iu',
            '#^\s*Etsy\s*[:\-–—|]\s*#iu',
            '#\s*[:\-–—|]\s*Etsy\s*$#iu',
        ];

        foreach ($patterns as $pattern) {
            $title = preg_replace($pattern, ' ', $title);
        }
        $title = preg_replace('#\s+#', ' ', $title);

        return trim($title);
    }

    /**
     * Show the form for creating a new equipment item.
     */
    public function create(): Response
    {
        Gate::authorize('create', Equipment::class);

        return Inertia::render('admin/equipment/Create');
    }

    /**
     * Store a newly created equipment item.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Equipment::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'image_url' => ['nullable', 'string', 'url', 'max:500'],
            'link' => ['required', 'url', 'max:500'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['nullable', 'string', 'max:50'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('equipment', 'public');
        } elseif (! empty($validated['image_url'] ?? null)) {
            $downloaded = $this->downloadImageFromUrl($validated['image_url']);
            if ($downloaded !== null) {
                $validated['image'] = $downloaded;
            } else {
                return redirect()->back()
                    ->withErrors(['image_url' => 'Bild konnte nicht von der URL geladen werden. Bitte Bild manuell hochladen.'])
                    ->withInput();
            }
        }
        unset($validated['image_url']);

        Equipment::create($validated);

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Equipment erfolgreich erstellt.');
    }

    /**
     * Download image from URL and store in public equipment folder. Returns storage path or null on failure.
     */
    private function downloadImageFromUrl(string $url): ?string
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => $userAgent,
                    'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                ])
                ->withOptions(['verify' => true])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $body = $response->body();
            $contentType = $response->header('Content-Type', '');
            $maxSize = 5 * 1024 * 1024; // 5MB
            if (strlen($body) > $maxSize) {
                return null;
            }

            $extension = 'jpg';
            if (preg_match('#image/(jpeg|jpg|png|gif|webp)#i', $contentType, $m)) {
                $ext = strtolower($m[1]);
                $extension = $ext === 'jpeg' ? 'jpg' : $ext;
            }

            $filename = 'equipment/'.uniqid('eq_', true).'.'.$extension;
            $stored = Storage::disk('public')->put($filename, $body);

            return $stored ? $filename : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Show the form for editing the specified equipment item.
     */
    public function edit(Equipment $equipment): Response
    {
        Gate::authorize('update', $equipment);

        if ($equipment->image && ! str_starts_with($equipment->image, 'http')) {
            $equipment->image = asset('storage/'.$equipment->image);
        }

        return Inertia::render('admin/equipment/Edit', [
            'equipment' => $equipment,
        ]);
    }

    /**
     * Update the specified equipment item.
     */
    public function update(Request $request, Equipment $equipment): RedirectResponse
    {
        Gate::authorize('update', $equipment);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'image_url' => ['nullable', 'string', 'url', 'max:500'],
            'link' => ['required', 'url', 'max:500'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['nullable', 'string', 'max:50'],
        ]);

        if ($request->hasFile('image')) {
            if ($equipment->image) {
                Storage::disk('public')->delete($equipment->image);
            }
            $validated['image'] = $request->file('image')->store('equipment', 'public');
        } elseif (! empty($validated['image_url'] ?? null)) {
            if ($equipment->image) {
                Storage::disk('public')->delete($equipment->image);
            }
            $downloaded = $this->downloadImageFromUrl($validated['image_url']);
            $validated['image'] = $downloaded;
        } elseif (array_key_exists('image', $request->all()) && $request->input('image') === null) {
            if ($equipment->image) {
                Storage::disk('public')->delete($equipment->image);
            }
            $validated['image'] = null;
        } else {
            unset($validated['image']);
        }
        unset($validated['image_url']);

        $equipment->update($validated);

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Equipment erfolgreich aktualisiert.');
    }

    /**
     * Remove the specified equipment item.
     */
    public function destroy(Equipment $equipment): RedirectResponse
    {
        Gate::authorize('delete', $equipment);

        // Bild löschen, falls vorhanden
        if ($equipment->image) {
            Storage::disk('public')->delete($equipment->image);
        }

        $equipment->delete();

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Equipment erfolgreich gelöscht.');
    }
}
