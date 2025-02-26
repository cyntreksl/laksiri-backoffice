<?php

use App\Http\Controllers\CourierAgentController;
use Illuminate\Support\Facades\Route;

Route::resource('courier-agents', CourierAgentController::class)
    ->except('show');
