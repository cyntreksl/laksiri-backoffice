<?php

use App\Http\Controllers\CourierController;
use Illuminate\Support\Facades\Route;

Route::resource('couriers', CourierController::class);
