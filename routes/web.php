<?php

use App\Http\Controllers\ConverterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\IngredientNoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Converter
Route::post('/convert', [ConverterController::class, 'convert'])->middleware('throttle:60,1')->name('convert');


// Ingredient Notes (CRUD)
Route::prefix('notes')->name('notes.')->middleware('throttle:60,1')->group(function () {
    Route::post('/', [IngredientNoteController::class, 'store'])->name('store');
    Route::put('/{note}', [IngredientNoteController::class, 'update'])->name('update');
    Route::delete('/{note}', [IngredientNoteController::class, 'destroy'])->name('destroy');
});



// Favorites
Route::prefix('favorites')->name('favorites.')->middleware('throttle:60,1')->group(function () {
    Route::post('/', [FavoriteController::class, 'store'])->name('store');
    Route::delete('/{favorite}', [FavoriteController::class, 'destroy'])->name('destroy');
});

// History
Route::prefix('history')->name('history.')->middleware('throttle:60,1')->group(function () {
    Route::post('/bulk-delete', [HistoryController::class, 'bulkDestroy'])->name('bulk-destroy');
    Route::delete('/', [HistoryController::class, 'clear'])->name('clear');
    Route::delete('/{history}', [HistoryController::class, 'destroy'])->name('destroy');
});
