<?php

use App\Models\Equipment;

use function Pest\Laravel\get;

test('guests can view equipment index', function (): void {
    Equipment::factory()->count(3)->create();

    get('/equipment')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('equipment/Index')
            ->has('equipment', 3)
            ->has('allCategories')
        );
});

test('equipment index returns all equipment for client-side filtering', function (): void {
    Equipment::factory()->create(['name' => 'WMF Messer', 'category' => 'Messer']);
    Equipment::factory()->create(['name' => 'Tefal Topf', 'category' => 'Töpfe']);
    Equipment::factory()->create(['name' => 'Anderes Gerät', 'category' => 'Elektro']);

    get('/equipment')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('equipment/Index')
            ->has('equipment', 3)
            ->has('allCategories')
        );
});
