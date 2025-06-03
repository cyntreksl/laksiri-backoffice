<?php

use App\Http\Controllers\ContainerPaymentController;
use Illuminate\Support\Facades\Route;

Route::resource('container-payment', ContainerPaymentController::class)
    ->except('show');

Route::get('container-payment/{container}', [ContainerPaymentController::class, 'getContainerPayment'])
    ->name('container-payment.getContainerPayment');

Route::get('container-payment-refund', [ContainerPaymentController::class, 'showContainerPaymentRefund'])
    ->name('container-payment.showContainerPaymentRefund');

Route::get('container-payment-list', [ContainerPaymentController::class, 'list']);

Route::get('container-payment-refund-list', [ContainerPaymentController::class, 'refundList']);

Route::post('container-payment/mark-refund-collection', [ContainerPaymentController::class, 'markRefundAsCollected'])
    ->name('container-payment.refund-collection');

Route::get('completed-container-payment', [ContainerPaymentController::class, 'showCompletedContainerPayment'])
    ->name('container-payment.showCompletedContainerPayment');

Route::get('container-payment-completed-list', [ContainerPaymentController::class, 'completedList']);

Route::get('payment-request-list', [ContainerPaymentController::class, 'paymentRequestList'])
    ->name('container-payment.request');

Route::get('get-container-payment-request-list', [ContainerPaymentController::class, 'requestList']);
