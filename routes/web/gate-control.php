<?php

use App\Http\Controllers\GateControlController;
use Illuminate\Support\Facades\Route;

Route::prefix('gate-control')->name('gate-control.')->group(function () {
    Route::get('/inbound-shipments', [GateControlController::class, 'listInboundShipments'])
        ->name('inbound-shipments.index');

    Route::post('/inbound-shipments/{container}', [GateControlController::class, 'updateInboundShipmentStatus'])
        ->name('inbound-shipments.update-status');
});
