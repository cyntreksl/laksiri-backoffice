<?php

use Illuminate\Support\Facades\Route;

Route::resource('hbls', App\Http\Controllers\HBLController::class)->except(['store', 'update', 'destroy', 'create', 'edit']);

Route::get('hbl-list', [App\Http\Controllers\HBLController::class, 'list']);

Route::get('approve-hbls', [App\Http\Controllers\Finance\HBLController::class, 'approveHBLs'])->name('hbls.approve-hbl');

Route::get('approve-hbl-list', [App\Http\Controllers\Finance\HBLController::class, 'list']);

Route::post('finance-approved', [App\Http\Controllers\Finance\HBLController::class, 'financeApproved'])
    ->name('hbls.finance-approved');
