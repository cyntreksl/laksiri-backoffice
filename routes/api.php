<?php

use App\Http\Controllers\Api\v1\Auth\LoginController;
use App\Http\Controllers\Api\v1\PickupController;
use App\Http\Controllers\Api\v1\HBLController;
use Illuminate\Support\Facades\Route;


Route::domain('api.'.config('app.url'))
    ->middleware(['auth:sanctum'])
    ->prefix('/v1/')->group(function () {
        Route::get('/pending-pickup-list', [PickupController::class, 'index']);
        Route::get('/pickups/{pickup}', [PickupController::class, 'show']);
        Route::post('/pickup-to-hbl/{pickUp}', [PickupController::class, 'pickupToHbl']);
        Route::apiResource('hbl', HBLController::class)->only(['store']);
    });

Route::domain('api.'.config('app.url'))->prefix('/v1/')->post('/login', [LoginController::class, 'login']);
