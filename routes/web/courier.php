<?php

use App\Http\Controllers\CourierAgentController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\ThirdPartyAgentController;
use Illuminate\Support\Facades\Route;

Route::prefix('couriers')->name('couriers.')->group(function () {
    Route::get('list', [CourierController::class, 'list'])
        ->name('list');

    Route::get('/', [CourierController::class, 'index'])->name('index');
    Route::get('/create', [CourierController::class, 'create'])->name('create');
    Route::post('/', [CourierController::class, 'store'])->name('store');
    Route::get('/{courier}', [CourierController::class, 'show'])->name('show');
    Route::get('/{courier}/edit', [CourierController::class, 'edit'])->name('edit');
    Route::put('/{courier}', [CourierController::class, 'update'])->name('update');
    Route::delete('/{courier}', [CourierController::class, 'destroy'])->name('destroy');

    Route::post('change-status', [CourierController::class, 'changeCourierStatus'])
        ->name('change-status')
        ->middleware('can:courier.edit');

    Route::get('/{courier}/download', [CourierController::class, 'download'])
        ->name('download')
        ->middleware('can:courier.download pdf');

    Route::get('/{courier}/download/invoice', [CourierController::class, 'downloadInvoice'])
        ->name('download.invoice')
        ->middleware('can:courier.download invoice');

    Route::prefix('third-party-agents')->name('agents.')->group(function () {
        Route::resource('/', ThirdPartyAgentController::class)
            ->parameters(['' => 'third_party_agent'])
            ->except('show');

        Route::get('list', [ThirdPartyAgentController::class, 'list'])
            ->name('list');
    });

    Route::prefix('courier-agents')->name('courier-agents.')->group(function () {
        Route::resource('/', CourierAgentController::class)
            ->parameters(['' => 'courier_agent'])
            ->except('show');

        Route::get('list', [CourierAgentController::class, 'list'])
            ->name('list');
    });
});
