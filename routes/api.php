<?php

use Illuminate\Support\Facades\Route;
use TomatoPHP\FilamentMediaManager\Http\Controllers\FolderController;

Route::middleware(config('filament-media-manager.api.middlewares'))->prefix(config('filament-media-manager.api.prefix'))->name('media-manager.')->group(function () {
    Route::get('/folders', [FolderController::class, 'index'])->name('folders.index');
    Route::get('/folders/{id}', [FolderController::class, 'show'])->name('folders.show');
});
