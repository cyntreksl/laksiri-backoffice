<?php

use App\Http\Controllers\Clearance\VesselScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('vessel-schedule')->name('vessel-schedule.')->group(function () {
    Route::get('/', [VesselScheduleController::class, 'index'])
        ->name('index');

    Route::get('/{vessel_schedule}', [VesselScheduleController::class, 'show'])
        ->name('show');

    Route::post('add-vessel/{vessel_schedule}', [VesselScheduleController::class, 'addContainerToSchedule'])
        ->name('add-vessel');

    Route::post('remove-vessel/{vessel_schedule}', [VesselScheduleController::class, 'removeContainerFromSchedule'])
        ->name('remove-vessel');

    Route::get('download/{vessel_schedule}', [VesselScheduleController::class, 'downloadVesselSchedulePDF'])
        ->name('download');

    Route::post('update-container/{container}', [VesselScheduleController::class, 'updateContainer'])
        ->name('update-container');

    Route::get('download-shipment-release-pdf/{container}', [VesselScheduleController::class, 'downloadShipmentReleasePDF'])
        ->name('download-shipment-release-pdf');
});
