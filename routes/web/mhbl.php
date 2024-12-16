<?php

use App\Http\Controllers\MHBLController;
use Illuminate\Support\Facades\Route;

Route::resource('mhbls', MHBLController::class);

Route::get('mhbl-list', [MHBLController::class, 'list']);
