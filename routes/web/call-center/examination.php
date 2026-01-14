<?php

use App\Http\Controllers\CallCenter\ExaminationController;

Route::get('/examination/queue/list', [ExaminationController::class, 'getExaminationQueueList'])
    ->name('examination.queue.list');

Route::get('/examination/{customer_queue}', [ExaminationController::class, 'create'])
    ->name('examination.create');

Route::post('/examination', [ExaminationController::class, 'store'])
    ->name('examination.store');

Route::get('/examination/show/gate-pass', [ExaminationController::class, 'showGatePassList'])
    ->name('examination.show.gate-pass');

Route::get('/examination/gate-pass/list', [ExaminationController::class, 'getGatePassList']);

Route::get('/examination/return-to-bond', [ExaminationController::class, 'showReturnToBond'])
    ->name('examination.return-to-bond');

Route::post('/examination/return-to-bond', [ExaminationController::class, 'returnToBond'])
    ->name('examination.return-to-bond.store');

Route::post('/examination/complete-token', [ExaminationController::class, 'completeToken'])
    ->name('examination.complete-token');
