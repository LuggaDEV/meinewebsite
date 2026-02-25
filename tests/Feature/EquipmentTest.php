<?php

use App\Models\Equipment;

use function Pest\Laravel\get;

test('guests can view equipment index', function (): void {
    Equipment::factory()->count(3)->create();

    get('/equipment')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('equipment/Index')
            ->has('equipment')
            ->has('filters')
            ->has('allCategories')
            ->where('filters.search', '')
            ->where('filters.categories', [])
        );
});

test('equipment index filters by search', function (): void {
    Equipment::factory()->create(['name' => 'WMF Messer', 'description' => 'Scharf']);
    Equipment::factory()->create(['name' => 'Tefal Topf', 'description' => 'Groß']);
    Equipment::factory()->create(['name' => 'Anderes Gerät', 'description' => 'Sonstiges']);

    get('/equipment?search=Messer')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('equipment/Index')
            ->has('equipment.data', 1)
            ->where('filters.search', 'Messer')
            ->where('equipment.data.0.name', 'WMF Messer')
        );
});

test('equipment index filters by category', function (): void {
    Equipment::factory()->create(['name' => 'Messer A', 'category' => 'Messer']);
    Equipment::factory()->create(['name' => 'Messer B', 'category' => 'Messer']);
    Equipment::factory()->create(['name' => 'Topf C', 'category' => 'Töpfe']);

    get('/equipment?categories=Messer')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('equipment/Index')
            ->has('equipment.data', 2)
            ->where('filters.categories', ['Messer'])
        );
});

test('equipment index filters by search and category combined', function (): void {
    Equipment::factory()->create(['name' => 'WMF Messer', 'category' => 'Messer']);
    Equipment::factory()->create(['name' => 'Anderes Messer', 'category' => 'Messer']);
    Equipment::factory()->create(['name' => 'WMF Topf', 'category' => 'Töpfe']);

    get('/equipment?search=WMF&categories=Messer')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('equipment/Index')
            ->has('equipment.data', 1)
            ->where('equipment.data.0.name', 'WMF Messer')
            ->where('filters.search', 'WMF')
            ->where('filters.categories', ['Messer'])
        );
});
