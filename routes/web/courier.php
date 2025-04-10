<?php

use App\Http\Controllers\CourierController;
use App\Http\Controllers\ThirdPartyAgentController;
use Illuminate\Support\Facades\Route;

Route::prefix('couriers')->name('couriers.')->group(function () {
    Route::resource('/', CourierController::class)
        ->parameters(['' => 'courier'])
        ->except('show');

    Route::get('list', [CourierController::class, 'list'])
        ->name('list');

    Route::post('change-status', [CourierController::class, 'changeCourierStatus'])
        ->name('change-status');

    Route::prefix('third-party-agents')->name('agents.')->group(function () {
        Route::resource('/', ThirdPartyAgentController::class)
            ->parameters(['' => 'third_party_agent'])
            ->except('show');

        Route::get('list', [ThirdPartyAgentController::class, 'list'])
            ->name('list');
    });
});
