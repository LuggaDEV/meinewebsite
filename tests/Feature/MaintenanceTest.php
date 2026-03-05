<?php

use App\Models\User;
use App\Services\MaintenanceService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->maintenance = app(MaintenanceService::class);
    $this->maintenance->set(false, null, null);
});

test('when maintenance is disabled guests see normal home page', function (): void {
    get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('recipes/Index'));
});

test('when maintenance is enabled guests see maintenance page', function (): void {
    $this->maintenance->set(true, null, 'Wir sind gleich wieder da.');

    get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Maintenance')
            ->has('message')
            ->where('message', 'Wir sind gleich wieder da.')
        );
});

test('when maintenance is enabled authenticated user can access home', function (): void {
    $this->maintenance->set(true, null, null);
    $user = User::factory()->create();

    actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('recipes/Index'));
});

test('when maintenance is enabled authenticated user can access admin maintenance', function (): void {
    $this->maintenance->set(true, null, null);
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('admin.maintenance.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/maintenance/Edit')
            ->has('enabled')
            ->has('ends_at')
            ->has('message')
        );
});

test('admin can enable maintenance with message and ends_at', function (): void {
    $user = User::factory()->create();
    $endsAt = now()->addHours(2)->format('Y-m-d\TH:i');

    actingAs($user)
        ->put(route('admin.maintenance.update'), [
            'enabled' => true,
            'message' => 'Kurz Wartung.',
            'ends_at' => $endsAt,
        ])
        ->assertRedirect(route('admin.maintenance.edit'));

    expect($this->maintenance->isEnabled())->toBeTrue()
        ->and($this->maintenance->getMessage())->toBe('Kurz Wartung.')
        ->and($this->maintenance->getEndsAt())->not->toBeNull();
});

test('admin can disable maintenance', function (): void {
    $this->maintenance->set(true, null, 'Message');
    $user = User::factory()->create();

    actingAs($user)
        ->put(route('admin.maintenance.update'), [
            'enabled' => false,
            'message' => null,
            'ends_at' => null,
        ])
        ->assertRedirect(route('admin.maintenance.edit'));

    expect($this->maintenance->isEnabled())->toBeFalse();
});

test('maintenance page receives ends_at when set and in future', function (): void {
    $endsAt = now()->addHour();
    $this->maintenance->set(true, $endsAt, 'Back in an hour.');

    get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Maintenance')
            ->where('message', 'Back in an hour.')
            ->has('endsAt')
        );
});
