<?php

use App\Http\Controllers\MHBLController;
use Illuminate\Support\Facades\Route;

Route::resource('mhbls', MHBLController::class);

Route::get('mhbl-list', [MHBLController::class, 'list']);

Route::post('mhbls/add-hbl', [MHBLController::class, 'addNewHBL']);

Route::get('mhbls/get-unloaded-mhbl/list', [MHBLController::class, 'getUnloadedMHBLs']);

Route::get('mhbls/get-container-loaded-mhbl/list', [MHBLController::class, 'getLoadedMHBLsToContainer']);
