<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplyRecipeReviewRequest;
use App\Models\Recipe;
use App\Models\RecipeReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecipeReviewController extends Controller
{
    public function index(): Response
    {
        $reviews = RecipeReview::query()
            ->with(['recipe:id,title', 'user:id,name'])
            ->latest()
            ->get()
            ->map(fn (RecipeReview $review) => [
                'id' => $review->id,
                'recipe_id' => $review->recipe_id,
                'recipe_title' => $review->recipe?->title,
                'rating' => $review->rating,
                'body' => $review->body,
                'author_name' => $review->author_name,
                'user' => $review->user ? ['id' => $review->user->id, 'name' => $review->user->name] : null,
                'reply' => $review->reply,
                'replied_at' => $review->replied_at?->toIso8601String(),
                'created_at' => $review->created_at->toIso8601String(),
            ]);

        return Inertia::render('admin/reviews/Index', [
            'reviews' => $reviews,
        ]);
    }

    public function reply(ReplyRecipeReviewRequest $request, Recipe $recipe, RecipeReview $review): RedirectResponse
    {
        if ($review->recipe_id !== $recipe->id) {
            abort(404);
        }

        $reply = $request->validated('reply');
        $reply = is_string($reply) && trim($reply) !== '' ? trim($reply) : null;

        $review->update([
            'reply' => $reply,
            'replied_at' => $reply !== null ? now() : null,
        ]);

        $returnToReviews = $request->boolean('return_to_reviews');

        return redirect()
            ->route($returnToReviews ? 'admin.reviews.index' : 'admin.recipes.edit', $returnToReviews ? [] : [$recipe])
            ->with('success', $reply !== null ? 'Antwort gespeichert.' : 'Antwort entfernt.');
    }

    public function destroy(Request $request, Recipe $recipe, RecipeReview $review): RedirectResponse
    {
        if ($review->recipe_id !== $recipe->id) {
            abort(404);
        }

        $review->delete();

        $returnToReviews = $request->boolean('return_to_reviews');

        return redirect()
            ->route($returnToReviews ? 'admin.reviews.index' : 'admin.recipes.edit', $returnToReviews ? [] : [$recipe])
            ->with('success', 'Bewertung gelöscht.');
    }
}
