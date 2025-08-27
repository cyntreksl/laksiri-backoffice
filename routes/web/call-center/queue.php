<?php

use App\Http\Controllers\CallCenter\QueueController;

Route::get('/queue/screens/document-verification', [QueueController::class, 'showDocumentVerificationScreen'])
    ->name('queue.screens.document-verification');

Route::get('/queue/screens/cashier', [QueueController::class, 'showDocumentCashierScreen'])
    ->name('queue.screens.cashier');

Route::get('/queue/screens/examination', [QueueController::class, 'showExaminationScreen'])
    ->name('queue.screens.examination');

Route::get('/queue/screens/package', [QueueController::class, 'showPackageScreen'])
    ->name('queue.screens.package');

Route::get('/get-document-verification-queue', [QueueController::class, 'getDocumentVerificationQueue']);

Route::get('/get-cashier-queue', [QueueController::class, 'getCashierQueue']);

Route::get('/get-examination-queue', [QueueController::class, 'getExaminationQueue']);

Route::get('/get-package-queue', [QueueController::class, 'getPackageQueue']);

Route::get('/get-package-details-by-token/{token}', [QueueController::class, 'getPackageDetailsByToken'])
    ->name('package.details.by.token');

Route::post('/return-package', [QueueController::class, 'returnPackage'])
    ->name('package.return');
