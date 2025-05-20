<?php

use App\Http\Controllers\Clearance\VesselScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/vessel-schedule', [VesselScheduleController::class, 'index'])
    ->name('vessel-schedule.index');

Route::post('vessel-schedule/add-vessel/{VesselSchedule}', [VesselScheduleController::class, 'addVesselToSchedule'])
    ->name('vessel-schedule.add-vessel');

Route::post('vessel-schedule/remove-vessel/{VesselSchedule}', [VesselScheduleController::class, 'removeVesselFromSchedule'])
    ->name('vessel-schedule.remove-vessel');

Route::get('vessel-schedule/download/{VesselSchedule}', [VesselScheduleController::class, 'downloadVesselSchedulePDF'])
    ->name('vessel-schedule.download');

Route::post('vessel-schedule/update-container/{container}', [VesselScheduleController::class, 'updateContainer'])
    ->name('vessel-schedule.update-container');
