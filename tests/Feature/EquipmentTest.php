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

test('equipment index includes original_price and discount_percentage for discount display', function (): void {
    Equipment::factory()->create([
        'name' => 'Rabatt-Equipment',
        'price' => '13,99 €',
        'original_price' => '15,24 €',
        'discount_percentage' => '14',
    ]);

    get('/equipment')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('equipment/Index')
            ->has('equipment', 1)
            ->where('equipment.0.name', 'Rabatt-Equipment')
            ->where('equipment.0.price', '13,99 €')
            ->where('equipment.0.original_price', '15,24 €')
            ->where('equipment.0.discount_percentage', '14')
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

test('equipment:check-prices updates price and sets original_price when new price is lower', function (): void {
    $equipment = Equipment::factory()->create([
        'link' => 'https://www.amazon.de/dp/test',
        'price' => '29,99 €',
        'original_price' => null,
    ]);

    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
    <body>
        <span class="a-price-whole">19<span class="a-price-decimal">,</span></span>
        <span class="a-price-fraction">99</span>
        <span class="a-price a-text-price apex-basisprice-value"><span aria-hidden="true">29,99€</span></span>
    </body>
    </html>
    HTML;

    Http::fake([
        'https://www.amazon.de/dp/test' => Http::response($html, 200),
    ]);

    $this->artisan('equipment:check-prices')->assertSuccessful();

    $equipment->refresh();
    expect($equipment->price)->toBe('19,99 €')
        ->and($equipment->original_price)->toBe('29,99 €')
        ->and($equipment->last_price_checked_at)->not->toBeNull();
});

test('equipment:check-prices stores discount_percentage from Amazon savings span', function (): void {
    $equipment = Equipment::factory()->create([
        'link' => 'https://www.amazon.de/dp/discount',
        'price' => '29,99 €',
        'original_price' => null,
        'discount_percentage' => null,
    ]);

    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
    <body>
        <span class="a-size-large a-color-price savingsPercentage apex-savings-percentage">-14&nbsp;%</span>
        <span class="a-price-whole">25<span class="a-price-decimal">,</span></span>
        <span class="a-price-fraction">99</span>
        <span class="a-price a-text-price apex-basisprice-value"><span aria-hidden="true">29,99€</span></span>
    </body>
    </html>
    HTML;

    Http::fake([
        'https://www.amazon.de/dp/discount' => Http::response($html, 200),
    ]);

    $this->artisan('equipment:check-prices')->assertSuccessful();

    $equipment->refresh();
    expect($equipment->price)->toBe('25,99 €')
        ->and($equipment->original_price)->toBe('29,99 €')
        ->and($equipment->discount_percentage)->toBe('14');
});

test('equipment:check-prices extracts from corePriceDisplay_desktop_feature_div with UVP and savings', function (): void {
    $equipment = Equipment::factory()->create([
        'link' => 'https://www.amazon.de/dp/B0861CBGNN',
        'price' => '150,00 €',
        'original_price' => null,
        'discount_percentage' => null,
    ]);

    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
    <body>
        <div id="corePriceDisplay_desktop_feature_div" class="celwidget">
            <div class="a-section a-spacing-none aok-align-center">
                <span class="apex-savings-container">
                    <span class="a-size-large a-color-price savingPriceOverride savingsPercentage apex-savings-percentage">-26&nbsp;%</span>
                </span>
                <span class="a-price priceToPay apex-pricetopay-value">
                    <span class="a-price-whole">102<span class="a-price-decimal">,</span></span>
                    <span class="a-price-fraction">49</span>
                    <span class="a-price-symbol">€</span>
                </span>
            </div>
            <div class="a-section a-spacing-small">
                <span class="aok-relative">
                    <span class="a-size-small aok-offscreen"> UVP: 139,00&nbsp;€ </span>
                    <span class="a-size-small basisPrice">UVP:
                        <span class="a-price a-text-price apex-basisprice-value" data-a-strike="true">
                            <span class="a-offscreen">139,00€</span>
                            <span aria-hidden="true">139,00€</span>
                        </span>
                    </span>
                </span>
            </div>
        </div>
    </body>
    </html>
    HTML;

    Http::fake([
        'https://www.amazon.de/dp/B0861CBGNN' => Http::response($html, 200),
    ]);

    $this->artisan('equipment:check-prices')->assertSuccessful();

    $equipment->refresh();
    expect($equipment->price)->toBe('102,49 €')
        ->and($equipment->original_price)->toBe('139,00 €')
        ->and($equipment->discount_percentage)->toBe('26');
});

test('equipment:check-prices uses Amazon Statt/original price when present', function (): void {
    $equipment = Equipment::factory()->create([
        'link' => 'https://www.amazon.de/dp/statt',
        'price' => '20,00 €',
        'original_price' => null,
        'discount_percentage' => null,
    ]);

    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
    <body>
        <span class="aok-relative">
            <span class="a-price a-text-price apex-basisprice-value" data-a-strike="true">
                <span class="a-offscreen">15,24€</span>
                <span aria-hidden="true">15,24€</span>
            </span>
        </span>
        <span class="savingsPercentage">-14&nbsp;%</span>
        <span class="a-price-whole">13<span class="a-price-decimal">,</span></span>
        <span class="a-price-fraction">99</span>
    </body>
    </html>
    HTML;

    Http::fake([
        'https://www.amazon.de/dp/statt' => Http::response($html, 200),
    ]);

    $this->artisan('equipment:check-prices')->assertSuccessful();

    $equipment->refresh();
    expect($equipment->price)->toBe('13,99 €')
        ->and($equipment->original_price)->toBe('15,24 €')
        ->and($equipment->discount_percentage)->toBe('14');
});

test('authenticated user can trigger equipment price check from admin', function (): void {
    $user = User::factory()->create();
    Equipment::factory()->create(['link' => 'https://example.com/product']);

    Http::fake(['https://example.com/product' => Http::response('<html><body></body></html>', 200)]);

    actingAs($user)
        ->post(route('admin.equipment.check-prices'))
        ->assertRedirect(route('admin.equipment.index'))
        ->assertSessionHas('success');
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
