<?php

use App\Http\Controllers\Admin\AboutController as AdminAboutController;
use App\Http\Controllers\Admin\RecipeController as AdminRecipeController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', [RecipeController::class, 'index'])->name('home');

Route::get('/recipe/{recipe}', [RecipeController::class, 'show'])->name('recipes.show');

Route::get('/impressum', function () {
    return Inertia::render('Impressum');
})->name('impressum');

Route::get('/datenschutz', function () {
    return Inertia::render('Datenschutz');
})->name('datenschutz');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminRecipeController::class, 'index'])->name('index');
    Route::get('/recipes', [AdminRecipeController::class, 'index'])->name('recipes.index');
    Route::get('/recipes/create', [AdminRecipeController::class, 'create'])->name('recipes.create');
    Route::post('/recipes', [AdminRecipeController::class, 'store'])->name('recipes.store');
    Route::get('/recipes/{recipe}/edit', [AdminRecipeController::class, 'edit'])->name('recipes.edit');
    Route::put('/recipes/{recipe}', [AdminRecipeController::class, 'update'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [AdminRecipeController::class, 'destroy'])->name('recipes.destroy');
    
    Route::get('/about/edit', [AdminAboutController::class, 'edit'])->name('about.edit');
    Route::put('/about', [AdminAboutController::class, 'update'])->name('about.update');
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
