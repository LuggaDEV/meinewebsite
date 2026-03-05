<?php

use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
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

test('authenticated user can fetch equipment metadata from URL', function (): void {
    $user = User::factory()->create();
    $url = 'https://example.com/product/kitchen-mixer';

    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
    <head>
        <meta property="og:title" content="KitchenAid Artisan Mixer" />
        <meta property="og:description" content="Der beliebte Küchenmixer für zu Hause." />
        <meta property="og:image" content="https://example.com/images/mixer.jpg" />
    </head>
    <body></body>
    </html>
    HTML;

    Http::fake([
        $url => Http::response($html, 200),
    ]);

    $response = actingAs($user)
        ->postJson(route('admin.equipment.fetch-from-url'), ['url' => $url]);

    $response->assertOk()
        ->assertJson([
            'name' => 'KitchenAid Artisan Mixer',
            'description' => 'Der beliebte Küchenmixer für zu Hause.',
            'image_url' => 'https://example.com/images/mixer.jpg',
        ]);
});

test('fetch-from-url prefers productDescription div over og:description on Amazon', function (): void {
    $user = User::factory()->create();
    $url = 'https://www.amazon.de/dp/example';

    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
    <head>
        <meta property="og:description" content="Kurze Meta-Beschreibung." />
    </head>
    <body>
        <div id="productDescription" class="a-section">
            <p><span>3-in-1-Messung für gesunde Pflanzen: Dieses Bodenfeuchtigkeitsmessgerät misst präzise.</span></p>
        </div>
    </body>
    </html>
    HTML;

    Http::fake([$url => Http::response($html, 200)]);

    $response = actingAs($user)
        ->postJson(route('admin.equipment.fetch-from-url'), ['url' => $url]);

    $response->assertOk()
        ->assertJson([
            'description' => '3-in-1-Messung für gesunde Pflanzen: Dieses Bodenfeuchtigkeitsmessgerät misst präzise.',
        ]);
});

test('fetch-from-url extracts price from Amazon a-price-whole and a-price-fraction', function (): void {
    $user = User::factory()->create();
    $url = 'https://www.amazon.de/dp/example';

    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
    <body>
        <span class="a-price-whole">13<span class="a-price-decimal">,</span></span>
        <span class="a-price-fraction">99</span>
    </body>
    </html>
    HTML;

    Http::fake([$url => Http::response($html, 200)]);

    $response = actingAs($user)
        ->postJson(route('admin.equipment.fetch-from-url'), ['url' => $url]);

    $response->assertOk()
        ->assertJson([
            'price' => '13,99 €',
        ]);
});

test('fetch-from-url uses img data-old-hires when og:image is missing', function (): void {
    $user = User::factory()->create();
    $url = 'https://www.amazon.de/dp/example';

    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
    <body>
        <img src="https://m.media-amazon.com/images/I/81abc._SX522_.jpg"
             data-old-hires="https://m.media-amazon.com/images/I/81abc._SL1500_.jpg"
             class="product-image">
    </body>
    </html>
    HTML;

    Http::fake([$url => Http::response($html, 200)]);

    $response = actingAs($user)
        ->postJson(route('admin.equipment.fetch-from-url'), ['url' => $url]);

    $response->assertOk()
        ->assertJson([
            'image_url' => 'https://m.media-amazon.com/images/I/81abc._SL1500_.jpg',
        ]);
});

test('fetch-from-url prefers productTitle span over og:title on Amazon', function (): void {
    $user = User::factory()->create();
    $url = 'https://www.amazon.de/dp/example';

    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
    <head>
        <meta property="og:title" content="Amazon.de: Wigearss Zigbee Bodenmessgerät" />
    </head>
    <body>
        <span id="productTitle" class="a-size-large">Wigearss Zigbee Bodenmessgerät, Feuchtigkeit, Temperatur</span>
    </body>
    </html>
    HTML;

    Http::fake([$url => Http::response($html, 200)]);

    $response = actingAs($user)
        ->postJson(route('admin.equipment.fetch-from-url'), ['url' => $url]);

    $response->assertOk()
        ->assertJson([
            'name' => 'Wigearss Zigbee Bodenmessgerät, Feuchtigkeit, Temperatur',
        ]);
});

test('fetch-from-url strips shop name from title when using og:title', function (): void {
    $user = User::factory()->create();
    $url = 'https://www.example-shop.de/product';

    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
    <head>
        <meta property="og:title" content="Amazon.de: WMF Küchenmesser Set 3-teilig" />
        <meta property="og:description" content="Produktbeschreibung." />
        <meta property="og:image" content="https://example.com/img.jpg" />
    </head>
    <body></body>
    </html>
    HTML;

    Http::fake([$url => Http::response($html, 200)]);

    $response = actingAs($user)
        ->postJson(route('admin.equipment.fetch-from-url'), ['url' => $url]);

    $response->assertOk()
        ->assertJson([
            'name' => 'WMF Küchenmesser Set 3-teilig',
            'image_url' => 'https://example.com/img.jpg',
        ]);
});

test('fetch-from-url returns 422 when page cannot be loaded', function (): void {
    $user = User::factory()->create();
    $url = 'https://example.com/not-found';

    Http::fake([
        $url => Http::response('Not Found', 404),
    ]);

    actingAs($user)
        ->postJson(route('admin.equipment.fetch-from-url'), ['url' => $url])
        ->assertStatus(422)
        ->assertJsonStructure(['message']);
});

test('authenticated user can create equipment with image_url', function (): void {
    $user = User::factory()->create();
    $imageUrl = 'https://example.com/images/equipment.jpg';
    $minimalPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);

    Http::fake([
        $imageUrl => Http::response($minimalPng, 200, ['Content-Type' => 'image/png']),
    ]);

    $response = actingAs($user)->post(route('admin.equipment.store'), [
        'name' => 'Test Equipment',
        'category' => 'Kochen',
        'link' => 'https://example.com/product',
        'description' => 'Test description',
        'image_url' => $imageUrl,
    ]);

    $response->assertRedirect(route('admin.equipment.index'));
    $equipment = Equipment::where('name', 'Test Equipment')->first();
    expect($equipment)->not->toBeNull()
        ->and($equipment->image)->not->toBeNull()
        ->and($equipment->link)->toBe('https://example.com/product');
});
