<?php

use App\Http\Controllers\CourierAgentController;
use Illuminate\Support\Facades\Route;

Route::resource('courier-agents', CourierAgentController::class)
    ->except('show');
Route::get('courier-agents/list', [CourierAgentController::class, 'list'])
    ->name('courier-agents.list');
