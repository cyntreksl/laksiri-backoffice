<?php

use App\Http\Controllers\CallCenter\HBLController;
use Illuminate\Support\Facades\Route;

Route::resource('hbls', HBLController::class);

Route::get('hbl-list', [HBLController::class, 'list']);

Route::get('/create-token/{hbl}', [HBLController::class, 'createToken'])
    ->name('hbls.create-token');

Route::get('hbls/show/door-to-door', [HBLController::class, 'showDoorToDoorList'])
    ->name('hbls.door-to-door-list');

Route::get('hbl-door-to-door-list', [HBLController::class, 'getDoorToDoorList']);
