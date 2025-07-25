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

Route::post('container-payment/approve', [ContainerPaymentController::class, 'approveContainerPayments'])
    ->name('container-payment.approve');

Route::get('approved-container-payments', [ContainerPaymentController::class, 'approvedContainerPayments'])
    ->name('approved-container-payments');

Route::get('approved-container-payments-list', [ContainerPaymentController::class, 'approvedList']);

Route::post('container-payment/revoke-approval', [ContainerPaymentController::class, 'revokeContainerPaymentsApprovals'])
    ->name('container-payment.revoke-approval');

Route::post('container-payment/complete-payment', [ContainerPaymentController::class, 'completePayment'])
    ->name('container-payment.complete-payment');

Route::post('/container-payment/approve-single', [ContainerPaymentController::class, 'approveSingle'])
    ->name('container-payment.approve-single');

Route::post('/container-payment/revoke-approval-single', [ContainerPaymentController::class, 'revokeSingle'])
    ->name('container-payment.revoke-approval-single');
