<?php

use App\Http\Controllers\DriverAreasController;
use App\Http\Controllers\ExceptionNameController;
use App\Http\Controllers\PackagePriceController;
use App\Http\Controllers\PackageTypeController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WarehouseZoneController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

Route::name('setting.')->group(function () {
    // Zones
    Route::get('zones/list', [ZoneController::class, 'list'])->name('driver-zones.list');

    Route::resource('zones', ZoneController::class)
        ->except(['create', 'show'])
        ->name('index', 'driver-zones.index');

    //Driver Areas
    Route::resource('driver-areas', DriverAreasController::class)
        ->except('show');

    Route::get('driver-areas/list', [DriverAreasController::class, 'list'])
        ->name('driver-area.list');

    //Warehouse Zones
    Route::get('warehouse-zones/list', [WarehouseZoneController::class, 'list'])
        ->name('warehouse-zones.list');

    Route::get('warehouse-zones', [WarehouseZoneController::class, 'index'])
        ->name('warehouse-zones.index');

    Route::delete('warehouse-zones/{id}', [WarehouseZoneController::class, 'delete'])
        ->name('warehouse-zones.delete');

    Route::post('warehousezones/create', [WarehouseZoneController::class, 'store'])
        ->name('warehouse-zones.store');

    Route::post('warehousezones/update', [WarehouseZoneController::class, 'update'])
        ->name('warehouse-zones.update');

    Route::get('warehousezones/{id}/edit', [WarehouseZoneController::class, 'edit'])
        ->name('warehouse-zones.edit');

    //Pricing
    Route::resource('prices', PriceController::class)->except('show');

    Route::resource('package-prices', PackagePriceController::class)->except('show');

    Route::resource('exception-names', ExceptionNameController::class);

    Route::resource('package-types', PackageTypeController::class)
        ->except(['show', 'create']);

    // Invoice Settings
    Route::post('invoice/settings', [SettingController::class, 'updateInvoiceSettings'])
        ->name('invoice.update');
});
