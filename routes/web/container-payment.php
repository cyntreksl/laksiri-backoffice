<?php

use App\Http\Controllers\ContainerPaymentController;
use Illuminate\Support\Facades\Route;

Route::resource('container-payment', ContainerPaymentController::class)
    ->except('show');

Route::get('container-payment/{container}', [ContainerPaymentController::class, 'getContainerPayment'])->name('container-payment.getContainerPayment');

Route::get('container-payment-list', [ContainerPaymentController::class, 'list']);
