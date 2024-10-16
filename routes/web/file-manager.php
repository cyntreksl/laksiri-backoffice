<?php

use App\Http\Controllers\FileController;

Route::get('/file-manager', [FileController::class, 'index'])
    ->name('file-manager.index');

Route::post('/file-manager', [FileController::class, 'upload'])
    ->name('file-manager.upload');

Route::get('/file-manager/downloads/{id}', [FileController::class, 'download'])
    ->name('file-manager.downloads.single');

Route::delete('/file-manager/{id}', [FileController::class, 'destroy'])
    ->name('file-manager.destroy');
