<?php

use App\Http\Controllers\BondStorageNumberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->prefix('bond-storage')->group(function () {
    Route::get('/', [BondStorageNumberController::class, 'index'])
        ->name('bond-storage.index');

    Route::post('/get-packages', [BondStorageNumberController::class, 'getShipmentPackages'])
        ->name('bond-storage.get-packages');

    Route::post('/generate', [BondStorageNumberController::class, 'generate'])
        ->name('bond-storage.generate');

    Route::post('/update-settings', [BondStorageNumberController::class, 'updateSettings'])
        ->name('bond-storage.update-settings');

    Route::post('/search-hbl', [BondStorageNumberController::class, 'searchHbl'])
        ->name('bond-storage.search-hbl');
});
