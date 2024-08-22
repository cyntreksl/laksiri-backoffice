<?php

use App\Http\Controllers\CallCenter\VerificationController;

Route::get('/package/queue/list', [VerificationController::class, 'getPackageQueueList'])
    ->name('package.queue.list');

Route::get('/package/{package_queue}', [VerificationController::class, 'create'])
    ->name('package.create');

Route::post('/package', [VerificationController::class, 'store'])
    ->name('package.store');

Route::get('/package/show/gate-pass', [VerificationController::class, 'showGatePassList'])
    ->name('package.show.gate-pass');

Route::get('/package/gate-pass/list', [VerificationController::class, 'getGatePassList']);
