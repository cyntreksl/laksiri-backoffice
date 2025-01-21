<?php

use App\Http\Controllers\CallCenter\DeliverController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::name('delivery.')->group(function () {
    //Delivery Warehouse
    Route::get('delivery-warehouses', function () {
        return Inertia::render('Delivery/DeliveryWarehouseList');
    })->name('delivery-warehouses.index');

    // Dispatch Point
    Route::get('dispatch-points', function () {
        return Inertia::render('Delivery/DispatchPointList');
    })->name('dispatch-points.index');

    //Dispatched Loads
    Route::get('dispatched-loads', function () {
        return Inertia::render('Delivery/DispatchedLoadList');
    })->name('dispatched-loads.index');

    Route::post('driver/assign', [DeliverController::class, 'assignDriver'])
        ->name('driver.assign');
});
