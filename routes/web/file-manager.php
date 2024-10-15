<?php

use App\Http\Controllers\FileController;

Route::get('/file-manager', [FileController::class, 'index'])
    ->name('file-manager.index');

Route::post('/file-manager', [FileController::class, 'upload'])
    ->name('file-manager.upload');
