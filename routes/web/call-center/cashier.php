<?php

use App\Http\Controllers\CallCenter\CashierController;

Route::get('/cashier/queue/list', [CashierController::class, 'getCashierQueueList'])
    ->name('cashier.queue.list');

Route::get('/cashier/show/paid', [CashierController::class, 'showPaidList'])
    ->name('cashier.show.paid');

Route::get('/cashier/paid/list', [CashierController::class, 'getPaidList']);

Route::get('/cashier/search-customers', [CashierController::class, 'searchCustomers']);

Route::get('/cashier/search-users', [CashierController::class, 'searchUsers']);

Route::get('/cashier/{customer_queue}', [CashierController::class, 'create'])
    ->name('cashier.create');

Route::post('/cashier', [CashierController::class, 'store'])
    ->name('cashier.store');
