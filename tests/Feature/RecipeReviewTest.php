<?php

use App\Models\Recipe;
use App\Models\RecipeReview;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

test('guests can store a recipe review', function (): void {
    $recipe = Recipe::factory()->create();

    post("/recipe/{$recipe->id}/reviews", [
        'rating' => 5,
        'body' => 'Sehr gut!',
    ])
        ->assertRedirect(route('recipes.show', $recipe));

    $this->assertDatabaseHas('recipe_reviews', [
        'recipe_id' => $recipe->id,
        'user_id' => null,
        'rating' => 5,
        'body' => 'Sehr gut!',
    ]);
});

test('authenticated users can store a recipe review', function (): void {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    actingAs($user)
        ->post("/recipe/{$recipe->id}/reviews", [
            'rating' => 5,
            'body' => 'Sehr lecker!',
        ])
        ->assertRedirect(route('recipes.show', $recipe));

    $this->assertDatabaseHas('recipe_reviews', [
        'recipe_id' => $recipe->id,
        'user_id' => $user->id,
        'rating' => 5,
        'body' => 'Sehr lecker!',
    ]);
});

test('store recipe review validates rating', function (): void {
    $recipe = Recipe::factory()->create();

    post("/recipe/{$recipe->id}/reviews", [
        'rating' => 10,
        'body' => null,
    ])
        ->assertSessionHasErrors('rating');

    post("/recipe/{$recipe->id}/reviews", [
        'rating' => 0,
        'body' => null,
    ])
        ->assertSessionHasErrors('rating');
});

test('store recipe review accepts optional body', function (): void {
    $recipe = Recipe::factory()->create();

    post("/recipe/{$recipe->id}/reviews", [
        'rating' => 4,
    ])
        ->assertRedirect(route('recipes.show', $recipe));

    $this->assertDatabaseHas('recipe_reviews', [
        'recipe_id' => $recipe->id,
        'user_id' => null,
        'rating' => 4,
        'body' => null,
    ]);
});

test('authenticated users can update their own review', function (): void {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();
    $review = RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => $user->id,
        'rating' => 3,
        'body' => 'Ok',
    ]);

    actingAs($user)
        ->put("/recipe/{$recipe->id}/reviews/{$review->id}", [
            'rating' => 5,
            'body' => 'Jetzt sehr gut!',
        ])
        ->assertRedirect(route('recipes.show', $recipe));

    $review->refresh();
    expect($review->rating)->toBe(5)
        ->and($review->body)->toBe('Jetzt sehr gut!');
});

test('authenticated users cannot update another users review', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $recipe = Recipe::factory()->create();
    $review = RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => $otherUser->id,
        'rating' => 3,
    ]);

    actingAs($user)
        ->put("/recipe/{$recipe->id}/reviews/{$review->id}", [
            'rating' => 1,
            'body' => 'Geändert',
        ])
        ->assertForbidden();

    $review->refresh();
    expect($review->rating)->toBe(3);
});

test('authenticated users can delete their own review', function (): void {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();
    $review = RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => $user->id,
    ]);

    actingAs($user)
        ->delete("/recipe/{$recipe->id}/reviews/{$review->id}")
        ->assertRedirect(route('recipes.show', $recipe));

    $this->assertDatabaseMissing('recipe_reviews', ['id' => $review->id]);
});

test('guests cannot update or delete reviews', function (): void {
    $recipe = Recipe::factory()->create();
    $review = RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => null,
    ]);

    put("/recipe/{$recipe->id}/reviews/{$review->id}", ['rating' => 1, 'body' => 'x'])
        ->assertRedirect('/login');

    delete("/recipe/{$recipe->id}/reviews/{$review->id}")
        ->assertRedirect('/login');
});

test('authenticated users cannot delete another users review', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $recipe = Recipe::factory()->create();
    $review = RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => $otherUser->id,
    ]);

    actingAs($user)
        ->delete("/recipe/{$recipe->id}/reviews/{$review->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('recipe_reviews', ['id' => $review->id]);
});

test('recipe show includes average rating and reviews', function (): void {
    $recipe = Recipe::factory()->create();
    $user = User::factory()->create();
    RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => $user->id,
        'rating' => 5,
        'body' => 'Super!',
    ]);

    get(route('recipes.show', $recipe))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('recipes/Show')
            ->has('recipe')
            ->where('recipe.average_rating', 5)
            ->where('recipe.reviews_count', 1)
            ->has('recipe.reviews', 1)
            ->where('recipe.reviews.0.rating', 5)
            ->where('recipe.reviews.0.body', 'Super!')
        );
});

test('recipe show displays guest reviews with Gast as name when no author_name', function (): void {
    $recipe = Recipe::factory()->create();
    RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => null,
        'rating' => 4,
        'body' => 'Anonymous review',
    ]);

    get(route('recipes.show', $recipe))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recipe.reviews', 1)
            ->where('recipe.reviews.0.rating', 4)
            ->where('recipe.reviews.0.body', 'Anonymous review')
            ->where('recipe.reviews.0.user.name', 'Gast')
        );
});

test('recipe show displays guest reviews with author_name when provided', function (): void {
    $recipe = Recipe::factory()->create();

    post("/recipe/{$recipe->id}/reviews", [
        'rating' => 5,
        'author_name' => 'Max Mustermann',
        'body' => 'Tolles Rezept!',
    ])
        ->assertRedirect(route('recipes.show', $recipe));

    get(route('recipes.show', $recipe))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recipe.reviews', 1)
            ->where('recipe.reviews.0.user.name', 'Max Mustermann')
            ->where('recipe.reviews.0.body', 'Tolles Rezept!')
        );
});

test('recipe show includes user_review when authenticated', function (): void {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();
    RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => $user->id,
        'rating' => 4,
        'body' => 'Meine Bewertung',
    ]);

    actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recipe.user_review')
            ->where('recipe.user_review.rating', 4)
            ->where('recipe.user_review.body', 'Meine Bewertung')
        );
});

test('recipe show includes admin reply when present', function (): void {
    $recipe = Recipe::factory()->create();
    RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => null,
        'rating' => 5,
        'body' => 'Super!',
        'reply' => 'Vielen Dank für dein Feedback!',
        'replied_at' => now(),
    ]);

    get(route('recipes.show', $recipe))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recipe.reviews', 1)
            ->where('recipe.reviews.0.reply', 'Vielen Dank für dein Feedback!')
            ->has('recipe.reviews.0.replied_at')
        );
});

test('admin can reply to a review', function (): void {
    $admin = User::factory()->create();
    $recipe = Recipe::factory()->create();
    $review = RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => null,
        'rating' => 4,
        'body' => 'Gut',
    ]);

    actingAs($admin)
        ->put("/admin/recipes/{$recipe->id}/reviews/{$review->id}/reply", [
            'reply' => 'Danke für deine Bewertung!',
        ])
        ->assertRedirect(route('admin.recipes.edit', $recipe));

    $review->refresh();
    expect($review->reply)->toBe('Danke für deine Bewertung!')
        ->and($review->replied_at)->not->toBeNull();
});

test('admin can remove reply by sending empty reply', function (): void {
    $admin = User::factory()->create();
    $recipe = Recipe::factory()->create();
    $review = RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => null,
        'rating' => 4,
        'reply' => 'Alte Antwort',
        'replied_at' => now(),
    ]);

    actingAs($admin)
        ->put("/admin/recipes/{$recipe->id}/reviews/{$review->id}/reply", [
            'reply' => '',
        ])
        ->assertRedirect(route('admin.recipes.edit', $recipe));

    $review->refresh();
    expect($review->reply)->toBeNull()
        ->and($review->replied_at)->toBeNull();
});

test('admin can delete a review', function (): void {
    $admin = User::factory()->create();
    $recipe = Recipe::factory()->create();
    $review = RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => null,
    ]);

    actingAs($admin)
        ->delete("/admin/recipes/{$recipe->id}/reviews/{$review->id}")
        ->assertRedirect(route('admin.recipes.edit', $recipe));

    $this->assertDatabaseMissing('recipe_reviews', ['id' => $review->id]);
});

test('authenticated user can access admin reviews index', function (): void {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();
    RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => null,
        'rating' => 5,
        'body' => 'Super Rezept!',
    ]);

    actingAs($user)
        ->get(route('admin.reviews.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reviews/Index')
            ->has('reviews', 1)
            ->where('reviews.0.recipe_title', $recipe->title)
            ->where('reviews.0.rating', 5)
            ->where('reviews.0.body', 'Super Rezept!')
        );
});

test('admin reply with return_to_reviews redirects to reviews index', function (): void {
    $admin = User::factory()->create();
    $recipe = Recipe::factory()->create();
    $review = RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => null,
        'rating' => 4,
    ]);

    actingAs($admin)
        ->put("/admin/recipes/{$recipe->id}/reviews/{$review->id}/reply?return_to_reviews=1", [
            'reply' => 'Danke!',
        ])
        ->assertRedirect(route('admin.reviews.index'));

    $review->refresh();
    expect($review->reply)->toBe('Danke!');
});

test('admin delete with return_to_reviews redirects to reviews index', function (): void {
    $admin = User::factory()->create();
    $recipe = Recipe::factory()->create();
    $review = RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => null,
    ]);

    actingAs($admin)
        ->delete("/admin/recipes/{$recipe->id}/reviews/{$review->id}?return_to_reviews=1")
        ->assertRedirect(route('admin.reviews.index'));

    $this->assertDatabaseMissing('recipe_reviews', ['id' => $review->id]);
});
