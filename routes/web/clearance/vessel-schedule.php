<?php

use App\Http\Controllers\Clearance\VesselScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('vessel-schedule')->name('vessel-schedule.')->group(function () {
    Route::get('/', [VesselScheduleController::class, 'index'])
        ->name('index');

    Route::get('/{VesselSchedule}', [VesselScheduleController::class, 'show'])
        ->name('show');

    Route::post('add-vessel/{VesselSchedule}', [VesselScheduleController::class, 'addVesselToSchedule'])
        ->name('add-vessel');

    Route::post('remove-vessel/{VesselSchedule}', [VesselScheduleController::class, 'removeVesselFromSchedule'])
        ->name('remove-vessel');

    Route::get('download/{VesselSchedule}', [VesselScheduleController::class, 'downloadVesselSchedulePDF'])
        ->name('download');

    Route::post('update-container/{container}', [VesselScheduleController::class, 'updateContainer'])
        ->name('update-container');
});
