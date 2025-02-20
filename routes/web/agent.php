<?php

use App\Http\Controllers\ThirdPartyAgentController;
use Illuminate\Support\Facades\Route;

Route::resource('agents', ThirdPartyAgentController::class)
    ->except('show');
Route::get ('agents/{id}/edit', [ThirdPartyAgentController::class, 'edit'])
    ->name('agents.edit');
Route::put('agents/{id}', [ThirdPartyAgentController::class, 'update'])
    ->name('agents.update');

Route::delete('agents/{agents}', [ThirdPartyAgentController::class, 'destroy'])
       ->name('agents.destroy');
