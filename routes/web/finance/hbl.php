<?php

use Illuminate\Support\Facades\Route;

Route::get('approve-hbls', [App\Http\Controllers\Finance\HBLController::class, 'approveHBLs'])->name('hbls.approve-hbl');

Route::get('approved-hbls', [App\Http\Controllers\Finance\HBLController::class, 'approvedHBLs'])->name('hbls.approved-hbl');

Route::get('approve-hbl-list', [App\Http\Controllers\Finance\HBLController::class, 'list']);

Route::get('approved-hbl-list', [App\Http\Controllers\Finance\HBLController::class, 'approvedHBLList']);

Route::post('finance-approval', [App\Http\Controllers\Finance\HBLController::class, 'makeFinanceApproval'])
    ->name('hbls.finance-approved');

Route::post('remove-finance-approval', [App\Http\Controllers\Finance\HBLController::class, 'removeFinanceApproval'])
    ->name('hbls.remove-finance-approved');
