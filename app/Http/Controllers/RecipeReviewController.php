<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipeReviewRequest;
use App\Models\Recipe;
use App\Models\RecipeReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class RecipeReviewController extends Controller
{
    public function store(Recipe $recipe, StoreRecipeReviewRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $this->normalizeReviewData($request->validated(), $recipe->id, $user?->id);

        if ($user) {
            Gate::authorize('create', RecipeReview::class);
            RecipeReview::updateOrCreate(
                [
                    'recipe_id' => $recipe->id,
                    'user_id' => $user->id,
                ],
                $data
            );
        } else {
            RecipeReview::create($data);
        }

        return redirect()->route('recipes.show', $recipe);
    }

    public function update(StoreRecipeReviewRequest $request, Recipe $recipe, RecipeReview $review): RedirectResponse
    {
        if ($review->recipe_id !== $recipe->id) {
            abort(404);
        }

        Gate::authorize('update', $review);

        $validated = $request->validated();
        $authorName = isset($validated['author_name']) && trim((string) $validated['author_name']) !== ''
            ? trim((string) $validated['author_name'])
            : null;

        $review->update([
            'rating' => $validated['rating'],
            'body' => $validated['body'] ?? null,
            'author_name' => $authorName,
        ]);

        return redirect()->route('recipes.show', $recipe);
    }

    public function destroy(Recipe $recipe, RecipeReview $review): RedirectResponse
    {
        if ($review->recipe_id !== $recipe->id) {
            abort(404);
        }

        Gate::authorize('delete', $review);

        $review->delete();

        return redirect()->route('recipes.show', $recipe);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeReviewData(array $validated, int $recipeId, ?int $userId): array
    {
        $authorName = isset($validated['author_name']) && trim((string) $validated['author_name']) !== ''
            ? trim((string) $validated['author_name'])
            : null;

        return array_merge($validated, [
            'recipe_id' => $recipeId,
            'user_id' => $userId,
            'author_name' => $authorName,
        ]);
    }
}
