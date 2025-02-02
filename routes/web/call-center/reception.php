<?php

use App\Http\Controllers\CallCenter\HBLController;
use App\Http\Controllers\CallCenter\ReceptionController;
use Illuminate\Support\Facades\Route;

Route::get('/reception/hbl-list', [HBLController::class, 'index'])
    ->name('reception.queue.hbl-list');

Route::get('/reception/queue/list', [ReceptionController::class, 'getReceptionQueueList'])
    ->name('reception.queue.list');

Route::get('/reception/show/verified', [ReceptionController::class, 'showReceptionList'])
    ->name('reception.show.verified');
