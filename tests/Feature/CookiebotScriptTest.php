<?php

use App\Models\User;
use App\Services\MaintenanceService;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    app(MaintenanceService::class)->set(false, null, null);
});

afterEach(function (): void {
    Config::set('cookiebot.enabled', false);
    Config::set('cookiebot.domain_group_id', '');
});

test('home page does not include cookiebot script when disabled', function (): void {
    Config::set('cookiebot.enabled', false);
    Config::set('cookiebot.domain_group_id', '');

    get('/')
        ->assertSuccessful()
        ->assertDontSee('consent.cookiebot.com', false);
});

test('home page includes cookiebot script when enabled', function (): void {
    Config::set('cookiebot.enabled', true);
    Config::set('cookiebot.domain_group_id', 'test-domain-group-id');

    get('/')
        ->assertSuccessful()
        ->assertSee('consent.cookiebot.com', false)
        ->assertSee('data-cbid="test-domain-group-id"', false);
});

test('admin page does not include cookiebot script when enabled', function (): void {
    Config::set('cookiebot.enabled', true);
    Config::set('cookiebot.domain_group_id', 'test-domain-group-id');

    $user = User::factory()->create();

    actingAs($user)
        ->get(route('admin.index'))
        ->assertSuccessful()
        ->assertDontSee('consent.cookiebot.com', false);
});
