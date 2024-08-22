<?php

use App\Http\Controllers\CallCenter\BonedAreaController;

Route::get('/package/queue/list', [BonedAreaController::class, 'getPackageQueueList'])
    ->name('package.queue.list');

Route::get('/package/{package_queue}', [BonedAreaController::class, 'create'])
    ->name('package.create');

Route::post('/package', [BonedAreaController::class, 'store'])
    ->name('package.store');

Route::get('/package/show/gate-pass', [BonedAreaController::class, 'showGatePassList'])
    ->name('package.show.gate-pass');

Route::get('/package/gate-pass/list', [BonedAreaController::class, 'getGatePassList']);
