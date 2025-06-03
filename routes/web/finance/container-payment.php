<?php

use App\Http\Controllers\Finance\ContainerPaymentController;
use Illuminate\Support\Facades\Route;

Route::post('container-payment/approve', [ContainerPaymentController::class, 'approveContainerPayments'])->name('container-payment.approve');

Route::get('approved-container-payments', [ContainerPaymentController::class, 'approvedContainerPayments'])->name('approved-container-payments');

Route::get('approved-container-payments-list', [ContainerPaymentController::class, 'approvedList']);

Route::post('container-payment/revoke-approval', [ContainerPaymentController::class, 'revokeContainerPaymentsApprovals'])->name('container-payment.revoke-approval');

Route::post('container-payment/complete-payment', [ContainerPaymentController::class, 'completePayment'])->name('container-payment.complete-payment');
