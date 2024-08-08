<?php

use App\Http\Controllers\CallCenter\VerificationController;

Route::get('/verification/{customer_queue}', [VerificationController::class, 'create'])
    ->name('verification.create');
