<?php

use App\Http\Controllers\Api\v1\Auth\LoginController;
use App\Http\Controllers\Api\v1\DriverController;
use App\Http\Controllers\Api\v1\HBLController;
use App\Http\Controllers\Api\v1\PickupController;
use Illuminate\Support\Facades\Route;

Route::domain('api.'.config('app.url'))
    ->middleware(['auth:sanctum'])
    ->prefix('/v1/')->group(function () {
        Route::get('/pending-pickup-list', [PickupController::class, 'index']);
        Route::get('/pickups/{pickup}', [PickupController::class, 'show']);
        Route::get('/pickups/completed/list', [PickupController::class, 'completedPickupWithHBL']);
        Route::post('/pickup-to-hbl/{pickUp}', [PickupController::class, 'pickupToHbl']);
        Route::post('/pickups', [PickupController::class, 'store']);
        Route::apiResource('hbls', HBLController::class)->only(['store', 'show']);
        Route::post('/pickups/exceptions/{pickup}', [PickupController::class, 'storePickupException']);
        Route::get('/pickups/exceptions/list', [PickupController::class, 'getPickupExceptions']);
    });

Route::domain('api.'.config('app.url'))->prefix('/v1/')->post('/login', [LoginController::class, 'login']);

//Driver
Route::domain('api.'.config('app.url'))->middleware(['auth:sanctum'])->prefix('/v1/')->group(function () {
    Route::post('/driver/update', [DriverController::class, 'store']);
});
