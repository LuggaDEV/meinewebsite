<?php

namespace App\Http\Controllers;

use App\Models\About;
use App\Models\Recipe;
use App\Services\InstagramService;
use Inertia\Inertia;
use Inertia\Response;

class RecipeController extends Controller
{
    /**
     * Display a listing of recipes.
     */
    public function index(InstagramService $instagram): Response
    {
        $recipes = Recipe::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->get()
            ->map(function ($recipe) {
                if ($recipe->image && ! str_starts_with($recipe->image, 'http')) {
                    $recipe->image = asset('storage/'.$recipe->image);
                }
                $recipe->average_rating = round((float) ($recipe->reviews_avg_rating ?? 0), 1);
                $recipe->reviews_count = $recipe->reviews_count;
                $recipe->makeHidden('reviews_avg_rating');

                return $recipe;
            });

        $about = About::first();
        if ($about && $about->image && ! str_starts_with($about->image, 'http')) {
            $about->image = asset('storage/'.$about->image);
        }

        $instagramFeed = $instagram->getMedia(12);

        return Inertia::render('recipes/Index', [
            'recipes' => $recipes,
            'about' => $about,
            'instagramFeed' => $instagramFeed,
        ]);
    }

    /**
     * Display the specified recipe.
     */
    public function show(Recipe $recipe): Response
    {
        if ($recipe->image && ! str_starts_with($recipe->image, 'http')) {
            $recipe->image = asset('storage/'.$recipe->image);
        }

        $recipe->load([
            'reviews' => fn ($q) => $q->with('user:id,name')->latest(),
        ]);
        $recipe->loadCount('reviews');
        $recipe->loadAvg('reviews', 'rating');

        $recipeData = $recipe->toArray();
        $recipeData['average_rating'] = round((float) ($recipe->reviews_avg_rating ?? 0), 1);
        $recipeData['reviews_count'] = $recipe->reviews_count;
        $recipeData['reviews'] = $recipe->reviews->map(fn ($r) => [
            'id' => $r->id,
            'rating' => $r->rating,
            'body' => $r->body,
            'created_at' => $r->created_at->toISOString(),
            'reply' => $r->reply,
            'replied_at' => $r->replied_at?->toISOString(),
            'user' => [
                'id' => $r->user?->id,
                'name' => $r->author_name ?? $r->user?->name ?? 'Gast',
            ],
        ])->values()->all();

        $userReview = null;
        if (auth()->check()) {
            $review = $recipe->reviews->firstWhere('user_id', auth()->id());
            if ($review) {
                $userReview = [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'body' => $review->body,
                    'author_name' => $review->author_name,
                    'created_at' => $review->created_at->toISOString(),
                    'user' => [
                        'id' => $review->user->id,
                        'name' => $review->author_name ?? $review->user->name,
                    ],
                ];
            }
        }
        $recipeData['user_review'] = $userReview;

        unset($recipeData['reviews_avg_rating']);

        return Inertia::render('recipes/Show', [
            'recipe' => $recipeData,
        ]);
    }
}
