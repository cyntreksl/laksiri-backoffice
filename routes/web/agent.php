<?php

use App\Http\Controllers\ThirdPartyAgentController;
use Illuminate\Support\Facades\Route;

Route::resource('agents', ThirdPartyAgentController::class)
    ->except('show');
Route::get('agents/list', [ThirdPartyAgentController::class, 'list'])
    ->name('agents.list');
