<?php

use App\Http\Controllers\Finance\ContainerPaymentController;
use Illuminate\Support\Facades\Route;

Route::resource('container-payments', ContainerPaymentController::class)
    ->except(['show', 'store', 'destroy', 'create', 'edit']);

Route::get('container-payments-list', [ContainerPaymentController::class, 'list']);

Route::post('container-payment/approve', [ContainerPaymentController::class, 'approveContainerPayments'])->name('container-payment.approve');
