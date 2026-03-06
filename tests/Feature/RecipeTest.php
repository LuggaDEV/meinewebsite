<?php

use App\Models\Recipe;
use App\Models\RecipeReview;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('guests can view recipes index', function (): void {
    Recipe::factory()->count(3)->create();

    get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('recipes/Index')
            ->has('recipes', 3)
            ->has('instagramFeed')
        );
});

test('recipes index includes instagramFeed as empty array when not configured', function (): void {
    config(['services.instagram.access_token' => null]);
    Cache::forget('instagram_feed');

    get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('recipes/Index')
            ->has('instagramFeed', 0)
        );
});

test('recipes index includes instagramFeed from Graph API when configured', function (): void {
    config(['services.instagram.access_token' => 'fake-token']);
    Cache::forget('instagram_feed');

    Http::fake([
        'graph.instagram.com/*' => Http::response([
            'data' => [
                [
                    'id' => 'media-1',
                    'media_type' => 'IMAGE',
                    'media_url' => 'https://example.com/img1.jpg',
                    'permalink' => 'https://www.instagram.com/p/abc/',
                    'caption' => 'Test post',
                ],
            ],
        ], 200),
    ]);

    get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('recipes/Index')
            ->has('instagramFeed', 1)
            ->where('instagramFeed.0.id', 'media-1')
            ->where('instagramFeed.0.media_url', 'https://example.com/img1.jpg')
            ->where('instagramFeed.0.permalink', 'https://www.instagram.com/p/abc/')
            ->where('instagramFeed.0.caption', 'Test post')
            ->where('instagramFeed.0.media_type', 'IMAGE')
        );
});

test('recipes index returns empty instagramFeed when Graph API fails', function (): void {
    config(['services.instagram.access_token' => 'fake-token']);
    Cache::forget('instagram_feed');

    Http::fake([
        'graph.instagram.com/*' => Http::response(['error' => 'Invalid token'], 401),
    ]);

    get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('recipes/Index')
            ->has('instagramFeed', 0)
        );
});

test('recipes index includes average_rating and reviews_count', function (): void {
    $recipe = Recipe::factory()->create();
    $user = User::factory()->create();
    RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => $user->id,
        'rating' => 5,
    ]);

    get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recipes')
            ->where('recipes.0.average_rating', 5)
            ->where('recipes.0.reviews_count', 1)
        );
});

test('guests can view a recipe', function (): void {
    $recipe = Recipe::factory()->create([
        'title' => 'Test Rezept',
        'description' => 'Test Beschreibung',
    ]);

    get("/recipe/{$recipe->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('recipes/Show')
            ->has('recipe')
            ->where('recipe.title', 'Test Rezept')
        );
});

test('admin recipe edit includes reviews', function (): void {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();
    RecipeReview::factory()->create([
        'recipe_id' => $recipe->id,
        'user_id' => null,
        'rating' => 5,
        'body' => 'Toll!',
    ]);

    actingAs($user)
        ->get(route('admin.recipes.edit', $recipe))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/recipes/Edit')
            ->has('recipe')
            ->has('reviews', 1)
            ->where('reviews.0.rating', 5)
            ->where('reviews.0.body', 'Toll!')
        );
});

test('authenticated users can access admin recipes index', function (): void {
    $user = User::factory()->create();
    Recipe::factory()->count(2)->create();

    actingAs($user)
        ->get('/admin/recipes')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/recipes/Index')
            ->has('recipes', 2)
        );
});

test('guests cannot access admin recipes index', function (): void {
    get('/admin/recipes')
        ->assertRedirect('/login');
});

test('authenticated users can create a recipe', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->post('/admin/recipes', [
            'title' => 'Neues Rezept',
            'description' => 'Beschreibung',
            'ingredients' => ['Zutat 1', 'Zutat 2'],
            'instructions' => ['Schritt 1', 'Schritt 2'],
        ])
        ->assertRedirect('/admin/recipes');

    $this->assertDatabaseHas('recipes', [
        'title' => 'Neues Rezept',
        'description' => 'Beschreibung',
    ]);
});

test('authenticated users can update a recipe', function (): void {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    actingAs($user)
        ->put("/admin/recipes/{$recipe->id}", [
            'title' => 'Aktualisiertes Rezept',
            'description' => $recipe->description,
            'ingredients' => $recipe->ingredients,
            'instructions' => $recipe->instructions,
        ])
        ->assertRedirect('/admin/recipes');

    $this->assertDatabaseHas('recipes', [
        'id' => $recipe->id,
        'title' => 'Aktualisiertes Rezept',
    ]);
});

test('authenticated users can delete a recipe', function (): void {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    actingAs($user)
        ->delete("/admin/recipes/{$recipe->id}")
        ->assertRedirect('/admin/recipes');

    $this->assertDatabaseMissing('recipes', [
        'id' => $recipe->id,
    ]);
});
