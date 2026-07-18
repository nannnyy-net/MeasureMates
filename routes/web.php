<?php

use App\Http\Controllers\ConverterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IngredientNoteController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\SavedRecipeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Converter
Route::post('/convert', [ConverterController::class, 'convert'])->middleware('throttle:60,1')->name('convert');

// Whole Recipe Converter Routes
Route::prefix('recipe')->name('recipe.')->middleware('throttle:60,1')->group(function () {
    Route::post('/analyze', [RecipeController::class, 'analyze'])->name('analyze');
    Route::post('/convert', [RecipeController::class, 'convert'])->name('convert');
});

Route::resource('saved-recipes', SavedRecipeController::class)->except(['create', 'edit', 'show'])->middleware('throttle:60,1');
Route::get('saved-recipes/{saved_recipe}/print', [SavedRecipeController::class, 'print'])->name('saved-recipes.print')->middleware('throttle:60,1');

// Cookbook/Favorites/History removed permanently.
Route::resource('notes', IngredientNoteController::class)->only(['store', 'update', 'destroy']);

Route::prefix('recipes')->name('recipes.')->middleware('throttle:60,1')->group(function () {
    // Print-only (still used by conversion UI)
    Route::post('/print-log', [RecipeController::class, 'printLog'])->name('print-log');
});


