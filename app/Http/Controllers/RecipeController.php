<?php

namespace App\Http\Controllers;

use App\Models\About;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecipeController extends Controller
{
    /**
     * Display a listing of recipes.
     */
    public function index(): Response
    {
        $recipes = Recipe::latest()->get()->map(function ($recipe) {
            if ($recipe->image && !str_starts_with($recipe->image, 'http')) {
                $recipe->image = asset('storage/' . $recipe->image);
            }
            return $recipe;
        });

        $about = About::first();
        if ($about && $about->image && !str_starts_with($about->image, 'http')) {
            $about->image = asset('storage/' . $about->image);
        }

        return Inertia::render('recipes/Index', [
            'recipes' => $recipes,
            'about' => $about,
        ]);
    }

    /**
     * Display the specified recipe.
     */
    public function show(Recipe $recipe): Response
    {
        if ($recipe->image && !str_starts_with($recipe->image, 'http')) {
            $recipe->image = asset('storage/' . $recipe->image);
        }

        return Inertia::render('recipes/Show', [
            'recipe' => $recipe,
        ]);
    }
}
