<?php

use App\Http\Controllers\CallCenter\QueueController;

Route::get('/queue', [QueueController::class, 'index'])
    ->name('queue.index');
