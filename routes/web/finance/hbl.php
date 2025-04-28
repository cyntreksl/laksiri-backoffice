<?php

use Illuminate\Support\Facades\Route;

Route::get('approve-hbls', [App\Http\Controllers\Finance\HBLController::class, 'approveHBLs'])->name('hbls.approve-hbl');

Route::get('approve-hbl-list', [App\Http\Controllers\Finance\HBLController::class, 'list']);

Route::post('finance-approved', [App\Http\Controllers\Finance\HBLController::class, 'financeApproved'])
    ->name('hbls.finance-approved');
