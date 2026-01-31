<?php

use App\Models\Recipe;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\delete;

test('guests can view recipes index', function (): void {
    Recipe::factory()->count(3)->create();

    get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('recipes/Index')
            ->has('recipes', 3)
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
