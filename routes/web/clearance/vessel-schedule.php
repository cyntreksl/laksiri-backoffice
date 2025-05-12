<?php

use App\Http\Controllers\Clearance\VesselScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/vessel-schedule', [VesselScheduleController::class, 'index'])
    ->name('vessel-schedule.index');

Route::post('vessel-schedule/add-vessel/{VesselSchedule}', [VesselScheduleController::class, 'addVesselToSchedule'])
    ->name('vessel-schedule.add-vessel');
