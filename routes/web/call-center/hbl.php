<?php

use App\Http\Controllers\CallCenter\HBLController;
use Illuminate\Support\Facades\Route;

Route::resource('hbls', HBLController::class);

Route::get('hbl-list', [HBLController::class, 'list']);
Route::get('call-center-hbl-list', [HBLController::class, 'receptionIndex'])->name('callcenter-list');
Route::get('all-calls-data', [HBLController::class, 'getAllCallsData']);

Route::get('appointments', [HBLController::class, 'appointmentList'])->name('appointments');
Route::get('followups', [HBLController::class, 'followupList'])->name('followups');
Route::get('all-calls', [HBLController::class, 'allCallsList'])->name('all-calls');

Route::get('/create-token/{hbl}', [HBLController::class, 'createToken'])
    ->name('hbls.create-token');

Route::post('/create-token/{hbl}', [HBLController::class, 'createTokenWithVerification'])
    ->name('hbls.create-token-with-verification');

Route::get('/download-token/{token}', [HBLController::class, 'downloadToken'])
    ->name('hbls.download-token');

Route::get('/print-token/{token}', [HBLController::class, 'printToken'])
    ->name('hbls.print-token');

Route::get('hbls/show/door-to-door', [HBLController::class, 'showDoorToDoorList'])
    ->name('hbls.door-to-door-list');

Route::get('hbl-door-to-door-list', [HBLController::class, 'getDoorToDoorList']);
