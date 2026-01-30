<?php

use App\Http\Controllers\CallCenter\CashierController;
use App\Http\Controllers\CallCenter\CashierReportController;

Route::get('/cashier/queue/list', [CashierController::class, 'getCashierQueueList'])
    ->name('cashier.queue.list');

Route::get('/cashier/show/paid', [CashierController::class, 'showPaidList'])
    ->name('cashier.show.paid');

Route::get('/cashier/paid/list', [CashierController::class, 'getPaidList']);

Route::get('/cashier/search-customers', [CashierController::class, 'searchCustomers']);

Route::get('/cashier/search-users', [CashierController::class, 'searchUsers']);

Route::get('/cashier/verification-info/{hblId}', [CashierController::class, 'getVerificationInfo']);

Route::get('/cashier/payment-status/{hblId}', [CashierController::class, 'getPaymentStatus']);

// Daily Collection Report Routes
Route::get('/cashier/reports/debug', [CashierReportController::class, 'debug']);

Route::get('/cashier/reports/daily-collection', [CashierReportController::class, 'index'])
    ->name('cashier.reports.daily-collection');

Route::get('/cashier/reports/data', [CashierReportController::class, 'getData']);

Route::get('/cashier/reports/export-pdf', [CashierReportController::class, 'exportPdf']);

Route::get('/cashier/reports/export-excel', [CashierReportController::class, 'exportExcel']);

Route::get('/cashier/reports/cashiers', [CashierReportController::class, 'getCashiers']);

Route::get('/cashier/{customer_queue}', [CashierController::class, 'create'])
    ->name('cashier.create');

Route::post('/cashier', [CashierController::class, 'store'])
    ->name('cashier.store');

