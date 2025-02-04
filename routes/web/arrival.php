<?php

use App\Http\Controllers\BondedWarehouseController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\UnloadingIssueController;
use Illuminate\Support\Facades\Route;

Route::name('arrival.')->group(function () {
    // Shipments Arrivals
    Route::get('shipments-arrivals', [ContainerController::class, 'showShipmentArrivals'])
        ->name('shipments-arrivals.index');

    Route::get('unloading-points/{container?}', [ContainerController::class, 'showUnloadingPoint'])
        ->name('unloading-points.index');

    Route::post('/unload-container/unload', [ContainerController::class, 'unloadContainer'])
        ->name('unload-container.unload');

    Route::post('/unload-container/reload', [ContainerController::class, 'reloadContainer'])
        ->name('unload-container.reload');

    Route::post('/unloading-points/create/unloading-issue', [ContainerController::class, 'storeUnloadingIssue'])
        ->name('unloading-points.create.unloading-issue');

    Route::get('/shipments-arrivals/containers/{container_id}', [ContainerController::class, 'markAsReachedContainer'])
        ->name('shipments-arrivals.containers.markAsReachedContainer');

    Route::get('shipments-arrivals/list/export', [ContainerController::class, 'exportShipmentArrivals']);

    // Bonded Warehouse
    Route::get('bonded-warehouses', [BondedWarehouseController::class, 'index'])
        ->name('bonded-warehouses.index');

    Route::get('bonded-warehouse-list', [BondedWarehouseController::class, 'list']);

    Route::get('hbl/mark-as-short-loading/{hbl_id}', [BondedWarehouseController::class, 'markAsShortLoading'])
        ->name('hbls.mark-as-short-loading');

    Route::get('bonded-warehouse/list/export', [BondedWarehouseController::class, 'export']);

    // Unloading Issues
    Route::get('unloading-issues', [UnloadingIssueController::class, 'index'])
        ->name('unloading-issues.index');

    Route::get('unloading-issues-list', [UnloadingIssueController::class, 'list']);

    Route::get('/get-unloading-issues-by-hbl/{hbl}', [UnloadingIssueController::class, 'getUnloadingIssuesByHbl']);
});
