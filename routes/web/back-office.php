<?php

use App\Http\Controllers\CashSettlementController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::name('back-office.')->group(function () {
    // Cash Settlements
    Route::get('cash-settlements', [CashSettlementController::class, 'index'])
        ->name('cash-settlements.index');
    Route::get('cash-settlement-list', [CashSettlementController::class, 'list'])
        ->name('cash-settlements.list');
    Route::post('cash-settlement-summery', [CashSettlementController::class, 'getSummery'])
        ->name('cash-settlements.summery');
    Route::post('cash-received', [CashSettlementController::class, 'cashReceived'])
        ->name('cash-settlements.cashReceived');
    Route::put('update/payments/{hbl}', [CashSettlementController::class, 'paymentUpdate'])
        ->name('cash-settlements.payment.update');
    Route::get('cash-settlements/export', [CashSettlementController::class, 'export']);
    Route::get('cash-settlements/due-payment/export', [CashSettlementController::class, 'duePaymentExport']);

    // Warehouse
    Route::get('warehouses', [WarehouseController::class, 'index'])
        ->name('warehouses.index');
    Route::get('get-warehouse-list', [WarehouseController::class, 'list'])
        ->name('warehouses.list');
    Route::post('warehouse-summery', [WarehouseController::class, 'getSummery'])
        ->name('warehouses.summery');
    Route::put('warehouses/{hbl}/assign-zones', [WarehouseController::class, 'assignZone'])
        ->name('warehouses.assign.zone');
    Route::get('warehouses/export', [WarehouseController::class, 'export']);
    Route::get('warehouses/download/barcode/{hbl}', [WarehouseController::class, 'downloadBarcode'])
        ->name('warehouses.download.barcode');
    Route::post('revert-to-cash-settlement', [WarehouseController::class, 'revertToCashSettlement']);

    // Due Payment
    Route::get('duepayments', [CashSettlementController::class, 'duePaymentIndex'])
        ->name('duepayments.duePaymentIndex');
    Route::get('duepayment-list', [CashSettlementController::class, 'duePaymentList'])
        ->name('duepayments.duePaymentList');
});
