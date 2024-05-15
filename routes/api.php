<?php

use App\Http\Controllers\Api\v1\Auth\LoginController;
use App\Http\Controllers\Api\v1\PickupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum',])->prefix('/v1/')->group(function () {
    Route::get('/pending-pickup-list', [PickupController::class, 'index']);
    Route::post('/pickup-to-hbl/{pickUp}', [PickupController::class, 'pickupToHbl']);
});

Route::post('/login', [LoginController::class, 'login']);
