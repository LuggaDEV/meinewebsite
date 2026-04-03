<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    /**
     * Show the form for editing the about section.
     */
    public function edit(): Response
    {
        Gate::authorize('create', \App\Models\About::class);

        $about = About::first();

        if ($about && $about->image && ! str_starts_with($about->image, 'http')) {
            $about->image = asset('storage/'.$about->image);
        }

        return Inertia::render('admin/about/Edit', [
            'about' => $about,
        ]);
    }

    /**
     * Update the about section.
     */
    public function update(Request $request): RedirectResponse
    {
        Gate::authorize('create', \App\Models\About::class);

        $careerTimelineForUpdate = $this->normalizedCareerTimelineInput($request);

        $data = $request->all();
        if ($careerTimelineForUpdate !== null) {
            $data['career_timeline'] = $careerTimelineForUpdate;
        }

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'remove_image' => ['nullable', 'boolean'],
        ];

        if ($careerTimelineForUpdate !== null) {
            $rules['career_timeline'] = ['array'];
            $rules['career_timeline.*.organization'] = ['required', 'string', 'max:255'];
            $rules['career_timeline.*.role'] = ['required', 'string', 'max:255'];
            $rules['career_timeline.*.period'] = ['required', 'string', 'max:255'];
            $rules['career_timeline.*.location'] = ['nullable', 'string', 'max:255'];
        }

        $validated = Validator::make($data, $rules)->validate();

        $about = About::first();
        $removeImage = $request->boolean('remove_image');

        if ($request->hasFile('image')) {
            // Altes Bild löschen, falls vorhanden
            if ($about && $about->image) {
                Storage::disk('public')->delete($about->image);
            }
            $validated['image'] = $request->file('image')->store('about', 'public');
        } elseif ($removeImage) {
            if ($about && $about->image) {
                Storage::disk('public')->delete($about->image);
            }
            $validated['image'] = null;
        } else {
            // Kein neues Bild, nicht entfernt: altes Bild beibehalten
            if ($about) {
                unset($validated['image']);
            }
        }

        unset($validated['remove_image']);

        if ($about) {
            $about->update($validated);
        } else {
            About::create($validated);
        }

        return redirect()->route('admin.about.edit')
            ->with('success', 'Über Mich Sektion erfolgreich aktualisiert.');
    }

    /**
     * @return list<array{organization: string, role: string, period: string, location: string|null}>|null
     */
    private function normalizedCareerTimelineInput(Request $request): ?array
    {
        if (! array_key_exists('career_timeline', $request->all())) {
            return null;
        }

        return collect($request->input('career_timeline', []))
            ->map(function (mixed $row): array {
                $row = is_array($row) ? $row : [];

                return [
                    'organization' => trim((string) ($row['organization'] ?? '')),
                    'role' => trim((string) ($row['role'] ?? '')),
                    'period' => trim((string) ($row['period'] ?? '')),
                    'location' => filled($row['location'] ?? null) ? trim((string) $row['location']) : null,
                ];
            })
            ->filter(fn (array $row): bool => $row['organization'] !== ''
                && $row['role'] !== ''
                && $row['period'] !== '')
            ->values()
            ->all();
    }
}
