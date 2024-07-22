<?php

use App\Http\Controllers\PickupController;
use App\Http\Controllers\PickupExceptionController;
use Illuminate\Support\Facades\Route;

Route::resource('pickups', PickupController::class)
    ->except('edit');

Route::get('pickup-list', [PickupController::class, 'list']);

Route::get('pickup-list-order', [PickupController::class, 'showPickupOrder'])
    ->name('pickups.ordering');

Route::put('pickup-list-update-order', [PickupController::class, 'updatePickupOrder'])
    ->name('pickups.update-order');

Route::post('pickups/driver/assign', [PickupController::class, 'assignDriver'])
    ->name('pickups.driver.assign');

Route::get('pickups/exceptions/list', [PickupExceptionController::class, 'index'])
    ->name('pickups.exceptions');

Route::get('pickup-exception-list', [PickupExceptionController::class, 'list']);

Route::post('pickups/exceptions/driver/assign', [PickupExceptionController::class, 'assignDriver'])
    ->name('pickups.exceptions.driver.assign');

Route::post('pickups/exceptions/delete', [PickupExceptionController::class, 'deleteExceptions'])
    ->name('pickups.exceptions.delete');

Route::get('pickups/list/export', [PickupController::class, 'export'])
    ->name('pickups.export');
