<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class RecipeController extends Controller
{
    /**
     * Display a listing of recipes for admin.
     */
    public function index(): Response
    {
        $recipes = Recipe::latest()->get()->map(function ($recipe) {
            if ($recipe->image && !str_starts_with($recipe->image, 'http')) {
                $recipe->image = asset('storage/' . $recipe->image);
            }
            return $recipe;
        });

        return Inertia::render('admin/recipes/Index', [
            'recipes' => $recipes,
        ]);
    }

    /**
     * Show the form for creating a new recipe.
     */
    public function create(): Response
    {
        Gate::authorize('create', Recipe::class);

        return Inertia::render('admin/recipes/Create');
    }

    /**
     * Store a newly created recipe.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Recipe::class);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'servings' => ['nullable', 'integer', 'min:1'],
            'prep_time' => ['nullable', 'integer', 'min:1'],
            'cook_time' => ['nullable', 'integer', 'min:1'],
            'rest_time' => ['nullable', 'integer', 'min:1'],
            'ingredients' => ['required', 'array'],
            'ingredients.*' => ['required', 'string'],
            'instructions' => ['required', 'array'],
            'instructions.*' => ['required', 'string'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('recipes', 'public');
        }

        Recipe::create($validated);

        return redirect()->route('admin.recipes.index')
            ->with('success', 'Rezept erfolgreich erstellt.');
    }

    /**
     * Show the form for editing the specified recipe.
     */
    public function edit(Recipe $recipe): Response
    {
        Gate::authorize('update', $recipe);
        if ($recipe->image && !str_starts_with($recipe->image, 'http')) {
            $recipe->image = asset('storage/' . $recipe->image);
        }

        return Inertia::render('admin/recipes/Edit', [
            'recipe' => $recipe,
        ]);
    }

    /**
     * Update the specified recipe.
     */
    public function update(Request $request, Recipe $recipe): RedirectResponse
    {
        Gate::authorize('update', $recipe);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'servings' => ['nullable', 'integer', 'min:1'],
            'prep_time' => ['nullable', 'integer', 'min:1'],
            'cook_time' => ['nullable', 'integer', 'min:1'],
            'rest_time' => ['nullable', 'integer', 'min:1'],
            'ingredients' => ['required', 'array'],
            'ingredients.*' => ['required', 'string'],
            'instructions' => ['required', 'array'],
            'instructions.*' => ['required', 'string'],
        ]);

        if ($request->hasFile('image')) {
            // Altes Bild löschen, falls vorhanden
            if ($recipe->image) {
                Storage::disk('public')->delete($recipe->image);
            }
            $validated['image'] = $request->file('image')->store('recipes', 'public');
        } elseif ($request->has('image') && $request->input('image') === null) {
            // Bild explizit entfernen
            if ($recipe->image) {
                Storage::disk('public')->delete($recipe->image);
            }
            $validated['image'] = null;
        } else {
            // Wenn kein neues Bild hochgeladen wurde, das alte Bild beibehalten
            unset($validated['image']);
        }

        $recipe->update($validated);

        return redirect()->route('admin.recipes.index')
            ->with('success', 'Rezept erfolgreich aktualisiert.');
    }

    /**
     * Remove the specified recipe.
     */
    public function destroy(Recipe $recipe): RedirectResponse
    {
        Gate::authorize('delete', $recipe);
        // Bild löschen, falls vorhanden
        if ($recipe->image) {
            Storage::disk('public')->delete($recipe->image);
        }

        $recipe->delete();

        return redirect()->route('admin.recipes.index')
            ->with('success', 'Rezept erfolgreich gelöscht.');
    }
}
