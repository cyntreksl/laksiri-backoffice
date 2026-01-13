<?php

use App\Http\Controllers\TokenCancellationController;
use App\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;

Route::get('tokens', [TokenController::class, 'index'])
    ->name('tokens.index');

Route::get('tokens/{token}', [TokenController::class, 'show'])
    ->name('tokens.show');

Route::get('/token-list', [TokenController::class, 'list']);

// Token cancellation routes
Route::post('tokens/{token}/cancel', [TokenCancellationController::class, 'cancel'])
    ->name('tokens.cancel')
    ->middleware('can:tokens.cancel');

Route::get('tokens/{token}/cancellation-eligibility', [TokenCancellationController::class, 'checkEligibility'])
    ->name('tokens.cancellation-eligibility')
    ->middleware('can:tokens.cancel');
