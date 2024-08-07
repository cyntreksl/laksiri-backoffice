<?php

use App\Http\Controllers\CallCenter\QueueController;

Route::get('/queue', [QueueController::class, 'index'])
    ->name('queue.index');

Route::get('/get-document-verification-queue', [QueueController::class, 'getDocumentVerificationQueue']);

Route::get('/get-cashier-queue', [QueueController::class, 'getCashierQueue']);

Route::get('/get-examination-queue', [QueueController::class, 'getExaminationQueue']);
