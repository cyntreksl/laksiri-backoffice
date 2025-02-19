<?php


use App\Http\Controllers\BranchController;
use App\Http\Controllers\ThirdPartyAgentController;
use Illuminate\Support\Facades\Route;

Route::resource('agents', ThirdPartyAgentController::class)
    ->except('show');



