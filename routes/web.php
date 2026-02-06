<?php

use App\Http\Controllers\Admin\AboutController as AdminAboutController;
use App\Http\Controllers\Admin\EquipmentController as AdminEquipmentController;
use App\Http\Controllers\Admin\RecipeController as AdminRecipeController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', [RecipeController::class, 'index'])->name('home');

Route::get('/recipe/{recipe}', [RecipeController::class, 'show'])->name('recipes.show');

Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment.index');

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

    Route::get('/equipment', [AdminEquipmentController::class, 'index'])->name('equipment.index');
    Route::get('/equipment/create', [AdminEquipmentController::class, 'create'])->name('equipment.create');
    Route::post('/equipment', [AdminEquipmentController::class, 'store'])->name('equipment.store');
    Route::get('/equipment/{equipment}/edit', [AdminEquipmentController::class, 'edit'])->name('equipment.edit');
    Route::put('/equipment/{equipment}', [AdminEquipmentController::class, 'update'])->name('equipment.update');
    Route::delete('/equipment/{equipment}', [AdminEquipmentController::class, 'destroy'])->name('equipment.destroy');
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
