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

    Route::get('/outbound-gate-passes', [GateControlController::class, 'listOutboundGatePasses'])
        ->name('outbound-gate-passes.index');

    Route::put('/outbound-gate-passes/{customer_queue}', [GateControlController::class, 'markAsDeparted'])
        ->name('outbound-gate-passes.mark-as-departed');

    Route::get('/complete-token', [GateControlController::class, 'showCompleteToken'])
        ->name('complete-token')
        ->middleware('can:gate-control.complete-token');

    Route::post('/complete-token', [GateControlController::class, 'completeToken'])
        ->name('complete-token.store')
        ->middleware('can:gate-control.complete-token');

    Route::get('/completed-tokens', [GateControlController::class, 'listCompletedTokens'])
        ->name('completed-tokens.index')
        ->middleware('can:gate-control.view-completed-tokens');

    Route::get('/completed-tokens/list', [GateControlController::class, 'getCompletedTokensList'])
        ->name('completed-tokens.list')
        ->middleware('can:gate-control.view-completed-tokens');
});
