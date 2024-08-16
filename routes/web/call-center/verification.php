<?php

use App\Http\Controllers\CallCenter\VerificationController;

Route::get('/verification/queue/list', [VerificationController::class, 'getVerificationQueueList'])
    ->name('verification.queue.list');

Route::get('/verification/{customer_queue}', [VerificationController::class, 'create'])
    ->name('verification.create');

Route::post('/verification', [VerificationController::class, 'store'])
    ->name('verification.store');

Route::get('/verification/show/verified', [VerificationController::class, 'showVerifiedList'])
    ->name('verification.show.verified');

Route::get('/verification/verified/list', [VerificationController::class, 'getVerifiedList']);
