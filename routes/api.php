<?php

use Illuminate\Support\Facades\Route;

Route::middleware(config('filament-media-manager.api.middlewares'))->prefix(config('filament-media-manager.api.prefix'))->name('media-manager.')->group(function () {
    Route::get('/folders', [\TomatoPHP\FilamentMediaManager\Http\Controllers\FolderController::class, 'index'])->name('folders.index');
    Route::get('/folders/{id}', [\TomatoPHP\FilamentMediaManager\Http\Controllers\FolderController::class, 'show'])->name('folders.show');
});
