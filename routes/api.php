<?php

use App\Http\Controllers\Api\v1\Auth\LoginController;
use App\Http\Controllers\Api\v1\DashboardController;
use App\Http\Controllers\Api\v1\DeviceTokenController;
use App\Http\Controllers\Api\v1\DriverController;
use App\Http\Controllers\Api\v1\ExceptionNameController;
use App\Http\Controllers\Api\v1\HBLController;
use App\Http\Controllers\Api\v1\HblImageController;
use App\Http\Controllers\Api\v1\PackageTypeController;
use App\Http\Controllers\Api\v1\PickupController;
use App\Http\Controllers\CallCenter\DeliverController;
use App\Http\Controllers\HandlingProcedureController;
use Illuminate\Support\Facades\Route;

Route::domain(app()->environment('local') ? null : 'api.'.parse_url(config('app.url'), PHP_URL_HOST))
    ->middleware(['auth:sanctum'])
    ->prefix('/v1/')->group(function () {
        Route::get('/pending-pickup-list', [PickupController::class, 'index']);

        Route::get('/pickups/{pickup}', [PickupController::class, 'show']);

        Route::get('/pickups/completed/list', [PickupController::class, 'completedPickupWithHBL']);
        Route::get('/completed/list', [HBLController::class, 'completedHBL']);
        Route::get('/completed-hbl/{id}', [HBLController::class, 'completedHBLView']);

        Route::post('/pickup-to-hbl/{pickUp}', [PickupController::class, 'pickupToHbl']);

        Route::post('/pickups', [PickupController::class, 'store']);

        Route::apiResource('hbls', HBLController::class)->only(['store', 'show']);

        Route::post('/hbls/calculate/payment', [HBLController::class, 'calculatePayment']);

        Route::post('/pickups/exceptions/{pickup}', [PickupController::class, 'storePickupException']);

        Route::get('/pickups/exceptions/list', [PickupController::class, 'getPickupExceptions']);

        Route::get('/pickups/exceptions/{exceptionId}', [PickupController::class, 'showPickupException']);

        Route::get('/exception-names', [ExceptionNameController::class, 'index']);

        Route::post('/driver/update', [DriverController::class, 'store']);

        Route::put('/driver/location/update/{user}', [DriverController::class, 'createDriverLocation']);

        Route::get('/package-type-list', [PackageTypeController::class, 'index']);

        Route::post('/get-hbl-rules', [HBLController::class, 'getHBLRules']);

        Route::post('/driver/change-password', [DriverController::class, 'updatePassword']);

        Route::get('/pending-deliver-list', [DeliverController::class, 'index']);

        Route::get('/delivers/{hblDeliver}', [DeliverController::class, 'show']);

        Route::post('/release-delivery', [DeliverController::class, 'releaseDeliverOrder']);

        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

        Route::post('/hbl-images/upload', [HblImageController::class, 'upload']);
        Route::post('/notifications/register-device', [DeviceTokenController::class, 'registerDevice']);

        Route::put('/update-hbl/{hbl}', [HBLController::class, 'update']);

        Route::get('/hbls/get-total-summary/{hbl}', [HBLController::class, 'getHBLTotalSummary'])->name('api.hbls.get-hbl-total-summary');
        Route::get('/hbls/get-destination-total-summary/{hbl}', [HBLController::class, 'getHBLDestinationTotalSummary'])->name('api.hbls.get-destination hbl-total-summary');

        Route::get('hbl-charge/{id}', [HBLController::class, 'hblChargeDetails'])
            ->name('hbl-charge.details');

    });

Route::domain('api.'.config('app.url'))->prefix('/v1/')->post('/login', [LoginController::class, 'login']);

Route::get('/containers/{container}/handling-procedures', [HandlingProcedureController::class, 'index'])->middleware(['web']);
Route::post('/containers/{container}/handling-procedures', [HandlingProcedureController::class, 'store'])->middleware(['web']);

// dev purposes only
Route::prefix('/v1/')->post('/login', [LoginController::class, 'login']);
Route::middleware(['auth:sanctum'])
    ->prefix('/v1/')->group(function () {
        Route::apiResource('hbls', HBLController::class)->only(['store', 'show']);
    });
