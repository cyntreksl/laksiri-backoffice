<?php

use App\Http\Controllers\RemarksController;
use Illuminate\Support\Facades\Route;

Route::get('/remarks/{type}/{id}', [RemarksController::class, 'index']);
