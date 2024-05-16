<?php

use App\Http\Controllers\Api\v1\Auth\LoginController;
use App\Http\Controllers\Api\v1\PickupController;
use Illuminate\Support\Facades\Route;

Route::domain('api.' . config('app.url'))
    ->middleware(['auth:sanctum',])
    ->prefix('/v1/')->group(function () {
    Route::get('/pending-pickup-list', [PickupController::class, 'index']);
});

Route::domain('api.' .  config('app.url'))->prefix('/v1/')->post('/login', [LoginController::class, 'login']);
