<?php

use App\Http\Controllers\CallCenter\QueueController;

Route::get('/queue/screens/document-verification', [QueueController::class, 'showDocumentVerificationScreen'])
    ->name('queue.screens.document-verification');

Route::get('/queue/screens/cashier', [QueueController::class, 'showDocumentCashierScreen'])
    ->name('queue.screens.cashier');

Route::get('/queue/screens/examination', [QueueController::class, 'showExaminationScreen'])
    ->name('queue.screens.examination');

Route::get('/get-document-verification-queue', [QueueController::class, 'getDocumentVerificationQueue']);

Route::get('/get-cashier-queue', [QueueController::class, 'getCashierQueue']);

Route::get('/get-examination-queue', [QueueController::class, 'getExaminationQueue']);
