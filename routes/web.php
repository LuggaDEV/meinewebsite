<?php

use App\Http\Controllers\Admin\AboutController as AdminAboutController;
use App\Http\Controllers\Admin\MaintenanceController as AdminMaintenanceController;
use App\Http\Controllers\Admin\RecipeController as AdminRecipeController;
use App\Http\Controllers\Admin\RecipeReviewController as AdminRecipeReviewController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RecipeExportController;
use App\Http\Controllers\RecipeReviewController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [RecipeController::class, 'index'])->name('home');

Route::get('/recipe/{recipe}', [RecipeController::class, 'show'])->name('recipes.show');

Route::get('/recipe/{recipe}/export/json', [RecipeExportController::class, 'json'])->name('recipes.export.json');

Route::post('/recipe/{recipe}/reviews', [RecipeReviewController::class, 'store'])->name('recipes.reviews.store');

Route::middleware(['auth'])->group(function (): void {
    Route::put('/recipe/{recipe}/reviews/{review}', [RecipeReviewController::class, 'update'])->name('recipes.reviews.update');
    Route::delete('/recipe/{recipe}/reviews/{review}', [RecipeReviewController::class, 'destroy'])->name('recipes.reviews.destroy');
});

Route::get('/impressum', function () {
    return Inertia::render('Impressum', [
        'seo' => [
            'title' => 'Impressum',
            'description' => 'Impressum und rechtliche Angaben zu Luca Themann – Kochen.',
            'url' => url('/impressum'),
            'type' => 'website',
        ],
    ]);
})->name('impressum');

Route::get('/datenschutz', function () {
    return Inertia::render('Datenschutz', [
        'seo' => [
            'title' => 'Datenschutz',
            'description' => 'Datenschutzerklärung und Informationen zur Verarbeitung personenbezogener Daten.',
            'url' => url('/datenschutz'),
            'type' => 'website',
        ],
    ]);
})->name('datenschutz');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminRecipeController::class, 'index'])->name('index');
    Route::get('/recipes', [AdminRecipeController::class, 'index'])->name('recipes.index');
    Route::get('/recipes/create', [AdminRecipeController::class, 'create'])->name('recipes.create');
    Route::post('/recipes', [AdminRecipeController::class, 'store'])->name('recipes.store');
    Route::get('/recipes/{recipe}/edit', [AdminRecipeController::class, 'edit'])->name('recipes.edit');
    Route::put('/recipes/{recipe}', [AdminRecipeController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [AdminRecipeController::class, 'destroy'])->name('recipes.destroy');
    Route::get('/reviews', [AdminRecipeReviewController::class, 'index'])->name('reviews.index');
    Route::put('/recipes/{recipe}/reviews/{review}/reply', [AdminRecipeReviewController::class, 'reply'])->name('recipes.reviews.reply');
    Route::delete('/recipes/{recipe}/reviews/{review}', [AdminRecipeReviewController::class, 'destroy'])->name('recipes.reviews.destroy');

    Route::get('/about/edit', [AdminAboutController::class, 'edit'])->name('about.edit');
    Route::put('/about', [AdminAboutController::class, 'update'])->name('about.update');

    Route::get('/maintenance', [AdminMaintenanceController::class, 'edit'])->name('maintenance.edit');
    Route::put('/maintenance', [AdminMaintenanceController::class, 'update'])->name('maintenance.update');
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
