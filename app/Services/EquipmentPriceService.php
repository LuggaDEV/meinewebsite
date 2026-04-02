<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EquipmentPriceService
{
    /**
     * Fetch product page and extract current price, original ("Statt") price and discount percentage.
     *
     * @return array{price: string|null, original_price: string|null, discount_percentage: string|null}
     */
    public function getPriceAndDiscountFromUrl(string $url): array
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'de-DE,de;q=0.9,en;q=0.8',
                ])
                ->get($url);

            if (! $response->successful()) {
                return ['price' => null, 'original_price' => null, 'discount_percentage' => null];
            }

            return $this->getPriceAndDiscountFromHtml($response->body());
        } catch (\Throwable $e) {
            return ['price' => null, 'original_price' => null, 'discount_percentage' => null];
        }
    }

    /**
     * Extract price, original price and discount percentage from already-fetched HTML.
     *
     * @return array{price: string|null, original_price: string|null, discount_percentage: string|null}
     */
    public function getPriceAndDiscountFromHtml(string $html): array
    {
        try {
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($dom);

            $contexts = $this->getAmazonPriceContextNodes($xpath);
            $contexts[] = null;

            $price = null;
            $winningContext = null;
            foreach ($contexts as $context) {
                $price = $this->extractPriceFromHtml($xpath, $context);
                if ($price !== null) {
                    $winningContext = $context;

                    break;
                }
            }

            if ($price === null) {
                $fromLd = $this->extractFromJsonLd($html);
                if ($fromLd['price'] !== null) {
                    return $fromLd;
                }

                return $this->extractFromHtmlRegexFallback($html);
            }

            $original = $this->extractOriginalPriceFromHtml($xpath, $winningContext)
                ?? $this->extractOriginalPriceFromHtml($xpath, null);
            $discount = $this->extractDiscountPercentageFromHtml($xpath, $winningContext)
                ?? $this->extractDiscountPercentageFromHtml($xpath, null);

            return [
                'price' => $price,
                'original_price' => $original,
                'discount_percentage' => $discount,
            ];
        } catch (\Throwable $e) {
            return ['price' => null, 'original_price' => null, 'discount_percentage' => null];
        }
    }

    /**
     * @deprecated Use getPriceAndDiscountFromUrl() instead.
     */
    public function getPriceFromUrl(string $url): ?string
    {
        $result = $this->getPriceAndDiscountFromUrl($url);

        return $result['price'];
    }

    /**
     * Parse "13,99 €" or "29,90 €" to float for comparison.
     */
    public function parsePriceToFloat(?string $price): ?float
    {
        if ($price === null || trim($price) === '') {
            return null;
        }
        $normalized = preg_replace('/[^\d,.]/', '', $price);
        $normalized = str_replace(',', '.', $normalized);
        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    /**
     * @return list<\DOMNode>
     */
    private function getAmazonPriceContextNodes(\DOMXPath $xpath): array
    {
        $ids = [
            'corePriceDisplay_desktop_feature_div',
            'corePrice_desktop',
            'desktop_accordion_buybox',
            'desktop_buybox',
            'priceblock_ourprice',
        ];

        $nodes = [];
        foreach ($ids as $id) {
            $el = $xpath->query("//*[@id='{$id}']")->item(0);
            if ($el instanceof \DOMNode) {
                $nodes[] = $el;
            }
        }

        return $nodes;
    }

    private function queryOne(\DOMXPath $xpath, ?\DOMNode $context, string $path): ?\DOMNode
    {
        if ($context !== null) {
            $relative = str_starts_with($path, '//') ? '.'.$path : $path;
            $node = $xpath->query($relative, $context)->item(0);
            if ($node instanceof \DOMNode) {
                return $node;
            }
        }

        $node = $xpath->query($path)->item(0);

        return $node instanceof \DOMNode ? $node : null;
    }

    private function extractPriceFromHtml(\DOMXPath $xpath, ?\DOMNode $context = null): ?string
    {
        $whole = $this->queryOne($xpath, $context, "//span[contains(concat(' ', normalize-space(@class), ' '), ' a-price-whole ')]");
        $fraction = $this->queryOne($xpath, $context, "//span[contains(concat(' ', normalize-space(@class), ' '), ' a-price-fraction ')]");
        if ($whole === null || $fraction === null) {
            $offscreen = $this->queryOne($xpath, $context, "//span[contains(concat(' ', normalize-space(@class), ' '), ' a-price ') and contains(concat(' ', normalize-space(@class), ' '), ' aok-offscreen ')]");
            if ($offscreen !== null) {
                $text = trim($offscreen->textContent ?? '');
                if (preg_match('/(\d{1,3}(?:\.\d{3})*,\d{2}|\d+,\d{2})\s*€?/u', str_replace("\xc2\xa0", ' ', $text), $m)) {
                    $price = str_replace('.', ',', $m[1]);

                    return $price.' €';
                }
            }

            return null;
        }
        $wholeText = trim($whole->textContent ?? '');
        $fractionText = trim($fraction->textContent ?? '');
        if ($wholeText === '' && $fractionText === '') {
            return null;
        }
        $wholeClean = rtrim(preg_replace('/\s+/', '', $wholeText), ',');
        $fractionClean = preg_replace('/\s+/', '', $fractionText);
        $price = $fractionClean !== '' ? $wholeClean.','.$fractionClean : $wholeClean;
        $price = trim($price);
        if ($price === '' || $price === ',') {
            return null;
        }

        return $price.' €';
    }

    /**
     * Extract original / "Statt" / "UVP" price from HTML (e.g. Amazon span.apex-basisprice-value "139,00€").
     */
    private function extractOriginalPriceFromHtml(\DOMXPath $xpath, ?\DOMNode $context = null): ?string
    {
        $node = $this->queryOne($xpath, $context, "//span[contains(concat(' ', normalize-space(@class), ' '), ' apex-basisprice-value ') or contains(concat(' ', normalize-space(@class), ' '), ' basisPrice ')]");
        if ($node === null) {
            return null;
        }
        $text = trim($node->textContent ?? '');
        if ($text === '') {
            return null;
        }
        if (preg_match('/(\d+(?:[.,]\d+)?)\s*€?/u', str_replace("\xc2\xa0", ' ', $text), $m)) {
            $price = str_replace('.', ',', $m[1]);

            return $price.' €';
        }

        return null;
    }

    /**
     * Extract discount percentage from HTML (e.g. Amazon span.savingsPercentage "-26 %").
     */
    private function extractDiscountPercentageFromHtml(\DOMXPath $xpath, ?\DOMNode $context = null): ?string
    {
        $node = $this->queryOne($xpath, $context, "//span[contains(concat(' ', normalize-space(@class), ' '), ' savings-percentage ') or contains(concat(' ', normalize-space(@class), ' '), ' savingsPercentage ') or contains(concat(' ', normalize-space(@class), ' '), ' apex-savings-percentage ')]");
        if ($node === null) {
            return null;
        }
        $text = trim($node->textContent ?? '');
        if ($text === '') {
            return null;
        }
        if (preg_match('/[-−]?\s*(\d+)\s*%?/u', $text, $m)) {
            $value = (int) $m[1];
            if ($value > 0 && $value <= 100) {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * @return array{price: string|null, original_price: string|null, discount_percentage: string|null}
     */
    private function extractFromJsonLd(string $html): array
    {
        $empty = ['price' => null, 'original_price' => null, 'discount_percentage' => null];
        if (! preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            return $empty;
        }

        foreach ($matches[1] as $raw) {
            $raw = trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($raw === '') {
                continue;
            }
            $decoded = json_decode($raw, true);
            if (! is_array($decoded)) {
                continue;
            }
            $found = $this->findProductOfferInJsonLd($decoded);
            if ($found !== null) {
                return $found;
            }
        }

        return $empty;
    }

    /**
     * @param  array<mixed>  $data
     * @return array{price: string|null, original_price: string|null, discount_percentage: string|null}|null
     */
    private function findProductOfferInJsonLd(array $data): ?array
    {
        if (isset($data['@graph']) && is_array($data['@graph'])) {
            foreach ($data['@graph'] as $node) {
                if (is_array($node)) {
                    $found = $this->findProductOfferInJsonLd($node);
                    if ($found !== null) {
                        return $found;
                    }
                }
            }
        }

        $types = $data['@type'] ?? null;
        $isProduct = $types === 'Product' || (is_array($types) && in_array('Product', $types, true));
        if ($isProduct) {
            $fromOffers = $this->extractPriceFromOffersBlock($data['offers'] ?? null);
            if ($fromOffers !== null) {
                return $fromOffers;
            }
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                $nested = $this->findProductOfferInJsonLd($value);
                if ($nested !== null) {
                    return $nested;
                }
            }
        }

        return null;
    }

    /**
     * @return array{price: string|null, original_price: string|null, discount_percentage: string|null}|null
     */
    private function extractPriceFromOffersBlock(mixed $offers): ?array
    {
        if (! is_array($offers)) {
            return null;
        }

        $candidates = isset($offers['@type']) ? [$offers] : $offers;
        if (! is_array($candidates)) {
            return null;
        }

        foreach ($candidates as $offer) {
            if (! is_array($offer)) {
                continue;
            }
            $currency = strtoupper((string) ($offer['priceCurrency'] ?? 'EUR'));
            if ($currency !== '' && $currency !== 'EUR') {
                continue;
            }

            $price = $offer['price'] ?? null;
            if (is_int($price) || is_float($price)) {
                return [
                    'price' => number_format((float) $price, 2, ',', '').' €',
                    'original_price' => null,
                    'discount_percentage' => null,
                ];
            }
            if (is_string($price) && $price !== '') {
                return [
                    'price' => $this->formatSchemaPriceToGerman($price),
                    'original_price' => null,
                    'discount_percentage' => null,
                ];
            }
        }

        return null;
    }

    private function formatSchemaPriceToGerman(string $price): string
    {
        $normalized = str_replace(',', '.', trim($price));
        if (! is_numeric($normalized)) {
            return $price.' €';
        }
        $float = (float) $normalized;
        $formatted = number_format($float, 2, ',', '');

        return $formatted.' €';
    }

    /**
     * @return array{price: string|null, original_price: string|null, discount_percentage: string|null}
     */
    private function extractFromHtmlRegexFallback(string $html): array
    {
        $empty = ['price' => null, 'original_price' => null, 'discount_percentage' => null];
        $htmlNorm = str_replace("\xc2\xa0", ' ', $html);

        if (preg_match('/"priceToPay"\s*:\s*\{[^}]{0,2000}?"displayAmount"\s*:\s*"([^"]+)"/us', $htmlNorm, $m)) {
            $candidate = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('/(\d{1,3}(?:\.\d{3})*,\d{2}|\d+,\d{2})\s*€?/u', $candidate, $pm)) {
                return [
                    'price' => str_replace('.', ',', $pm[1]).' €',
                    'original_price' => null,
                    'discount_percentage' => null,
                ];
            }
        }

        return $empty;
    }
}
