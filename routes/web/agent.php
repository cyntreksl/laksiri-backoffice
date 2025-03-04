<?php

use App\Http\Controllers\ThirdPartyAgentController;
use Illuminate\Support\Facades\Route;

Route::resource('third-party-agents', ThirdPartyAgentController::class)
    ->except('show');
Route::get('third-party-agents/list', [ThirdPartyAgentController::class, 'list'])
    ->name('agents.list');
