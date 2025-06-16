<?php

use App\Http\Controllers\GateControlController;
use Illuminate\Support\Facades\Route;

Route::prefix('gate-control')->name('gate-control.')->group(function () {
    Route::get('/inbound-shipments', [GateControlController::class, 'listInboundShipments'])
        ->name('inbound-shipments.index');

    Route::get('/get-after-dispatch-shipments-list', [GateControlController::class, 'getAfterDispatchShipmentsList']);

    Route::put('/inbound-shipments/{container}', [GateControlController::class, 'updateInboundShipmentStatus'])
        ->name('inbound-shipments.update-status');

    Route::get('/outbound-shipments', [GateControlController::class, 'listOutboundShipments'])
        ->name('outbound-shipments.index');

    Route::get('/get-after-inbound-shipments-list', [GateControlController::class, 'getAfterInboundShipmentsList']);

    Route::put('/outbound-shipments/{container}', [GateControlController::class, 'updateOutboundShipmentStatus'])
        ->name('outbound-shipments.update-status');
});
