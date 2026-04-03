<?php

use App\Models\About;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('admin can remove about section image', function (): void {
    $user = User::factory()->create();
    $about = About::create([
        'title' => 'Über mich',
        'content' => 'Mein Text.',
        'image' => 'about/old-image.jpg',
    ]);

    actingAs($user)
        ->put(route('admin.about.update'), [
            'title' => $about->title,
            'content' => $about->content,
            'remove_image' => true,
        ])
        ->assertRedirect(route('admin.about.edit'));

    $about->refresh();
    expect($about->image)->toBeNull();
});

test('admin can update about section without changing image', function (): void {
    $user = User::factory()->create();
    $about = About::create([
        'title' => 'Über mich',
        'content' => 'Mein Text.',
        'image' => 'about/kept-image.jpg',
    ]);

    actingAs($user)
        ->put(route('admin.about.update'), [
            'title' => 'Neuer Titel',
            'content' => $about->content,
        ])
        ->assertRedirect(route('admin.about.edit'));

    $about->refresh();
    expect($about->title)->toBe('Neuer Titel')
        ->and($about->image)->toBe('about/kept-image.jpg');
});

test('admin can save career timeline on about section', function (): void {
    $user = User::factory()->create();
    $about = About::create([
        'title' => 'Über mich',
        'content' => 'Mein Text.',
        'image' => null,
    ]);

    actingAs($user)
        ->put(route('admin.about.update'), [
            'title' => $about->title,
            'content' => $about->content,
            'career_timeline' => [
                [
                    'organization' => 'Oud Sluis',
                    'role' => 'Praktikum',
                    'period' => '2010',
                    'location' => 'Sluis, NL',
                ],
            ],
        ])
        ->assertRedirect(route('admin.about.edit'));

    $about->refresh();
    expect($about->career_timeline)->toBeArray()
        ->and($about->career_timeline[0]['organization'])->toBe('Oud Sluis')
        ->and($about->career_timeline[0]['role'])->toBe('Praktikum')
        ->and($about->career_timeline[0]['period'])->toBe('2010')
        ->and($about->career_timeline[0]['location'])->toBe('Sluis, NL');
});

test('recipes index includes career timeline on about when present', function (): void {
    config(['services.instagram.access_token' => null]);
    Cache::forget('instagram_feed');

    About::create([
        'title' => 'Über mich',
        'content' => 'Inhalt.',
        'image' => null,
        'career_timeline' => [
            [
                'organization' => 'Test Restaurant',
                'role' => 'Koch',
                'period' => '2020 – 2022',
                'location' => 'Berlin',
            ],
        ],
    ]);

    get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('recipes/Index')
            ->where('about.career_timeline.0.organization', 'Test Restaurant')
            ->where('about.career_timeline.0.role', 'Koch')
            ->where('about.career_timeline.0.period', '2020 – 2022'));
});
