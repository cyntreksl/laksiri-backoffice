<?php

use App\Http\Controllers\CallCenter\HBLController;
use Illuminate\Support\Facades\Route;

Route::resource('hbls', HBLController::class);

Route::get('hbl-list', [HBLController::class, 'list']);

Route::get('/create-token/{hbl}', [HBLController::class, 'createToken'])
    ->name('hbls.create-token');
