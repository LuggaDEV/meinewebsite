<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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
        
        if ($about && $about->image && !str_starts_with($about->image, 'http')) {
            $about->image = asset('storage/' . $about->image);
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
        
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
        ]);

        $about = About::first();
        
        if ($request->hasFile('image')) {
            // Altes Bild löschen, falls vorhanden
            if ($about && $about->image) {
                Storage::disk('public')->delete($about->image);
            }
            $validated['image'] = $request->file('image')->store('about', 'public');
        } else {
            // Wenn kein neues Bild hochgeladen wurde, das alte Bild beibehalten
            if ($about) {
                unset($validated['image']);
            }
        }

        if ($about) {
            $about->update($validated);
        } else {
            About::create($validated);
        }

        return redirect()->route('admin.about.edit')
            ->with('success', 'Über Mich Sektion erfolgreich aktualisiert.');
    }
}
