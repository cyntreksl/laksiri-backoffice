<?php

use App\Http\Controllers\CallCenter\HBLController;
use App\Http\Controllers\CallCenter\ReceptionController;
use Illuminate\Support\Facades\Route;

Route::get('/reception/hbl-list', [HBLController::class, 'index'])
    ->name('reception.queue.hbl-list');

Route::get('/reception/queue/list', [ReceptionController::class, 'getReceptionQueueList'])
    ->name('reception.queue.list');

Route::get('/reception/{customer_queue}', [ReceptionController::class, 'create'])
    ->name('reception.create');

Route::post('/reception', [ReceptionController::class, 'store'])
    ->name('reception.store');

Route::get('/reception/show/verified', [ReceptionController::class, 'showReceptionVerifiedList'])
    ->name('reception.show.verified');

Route::get('/reception/verified/list', [ReceptionController::class, 'getReceptionVerifiedList']);
