<?php

use App\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;

Route::get('tokens', [TokenController::class, 'index'])
    ->name('tokens.index');

Route::get('tokens/{token}', [TokenController::class, 'show'])
    ->name('tokens.show');

Route::get('/token-list', [TokenController::class, 'list']);
