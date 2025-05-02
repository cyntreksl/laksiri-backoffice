<?php

use App\Http\Controllers\CallCenter\DeliverController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::name('delivery.')->group(function () {
    // Delivery Warehouse
    Route::get('delivery-warehouses', function () {
        return Inertia::render('Delivery/DeliveryWarehouseList');
    })->name('delivery-warehouses.index');

    // Dispatch Point
    Route::get('dispatch-points', function () {
        return Inertia::render('Delivery/DispatchPointList');
    })->name('dispatch-points.index');

    // Dispatched Loads
    Route::get('dispatched-loads', function () {
        return Inertia::render('Delivery/DispatchedLoadList');
    })->name('dispatched-loads.index');

    Route::post('driver/assign', [DeliverController::class, 'assignDriver'])
        ->name('driver.assign');

    Route::get('deliver-list-order', [DeliverController::class, 'showDeliverOrder'])
        ->name('ordering');

    Route::put('deliver-list-update-order', [DeliverController::class, 'updateDeliverOrder'])
        ->name('update-order');

    Route::put('driver/unassign/{hbl}', [DeliverController::class, 'unassignDriver'])
        ->name('driver.unassign');
});
