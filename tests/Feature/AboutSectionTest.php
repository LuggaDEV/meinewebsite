<?php

use App\Models\About;
use App\Models\User;

use function Pest\Laravel\actingAs;

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
