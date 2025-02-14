<?php

use App\Http\Controllers\PickupController;
use App\Http\Controllers\PickupExceptionController;
use Illuminate\Support\Facades\Route;

Route::resource('pickups', PickupController::class);

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

Route::get('pickups/exceptions/list/export', [PickupExceptionController::class, 'export'])
    ->name('pickups.exceptions.export');

Route::get('pickups/exceptions/retry/{pickup}', [PickupExceptionController::class, 'retry'])
    ->name('pickups.exceptions.retry');

Route::get('pickups/get-pending-jobs-by-user/{user}', [PickupController::class, 'getPendingJobsByUser'])
    ->name('pickups.get-pending-jobs-by-user');

Route::get('pickups-all-list', [PickupController::class, 'allPickups'])
    ->name('pickups.all');

Route::get('pickup-exception-list', [PickupController::class, 'allPickupsExport']);

Route::post('pickups/delete', [PickupController::class, 'deletePickups'])
    ->name('pickups.delete');
