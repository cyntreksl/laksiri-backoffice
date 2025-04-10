<?php

use App\Http\Controllers\AirLineController;
use App\Http\Controllers\CurrencyRateController;
use App\Http\Controllers\DriverAreasController;
use App\Http\Controllers\ExceptionNameController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\PackagePriceController;
use App\Http\Controllers\PackageTypeController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SpecialDOChargeController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\WarehouseZoneController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

Route::name('setting.')->group(function () {
    // Zones
    Route::get('zones/list', [ZoneController::class, 'list'])->name('driver-zones.list');

    Route::resource('zones', ZoneController::class)
        ->except(['create', 'show'])
        ->name('index', 'driver-zones.index');

    // Driver Areas
    Route::resource('driver-areas', DriverAreasController::class)
        ->except('show');

    Route::get('driver-areas/list', [DriverAreasController::class, 'list'])
        ->name('driver-area.list');

    Route::resource('air-lines', AirLineController::class)->except('show', 'create', 'edit');

    Route::get('air-lines/list', [AirLineController::class, 'list'])
        ->name('air-lines.list');

    Route::get('air-lines/do-charges', [AirLineController::class, 'index'])
        ->name('air-lines.do-charges');

    //    Route::get('air-lines/do-charges/list', [AirLineController::class, 'doAirLineChargesList'])
    //        ->name('air-lines.do-charges.list');

    // Warehouse Zones
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

    // Pricing
    Route::resource('prices', PriceController::class)->except('show');

    Route::resource('package-prices', PackagePriceController::class)->except('show');

    Route::resource('exception-names', ExceptionNameController::class);

    Route::resource('package-types', PackageTypeController::class)
        ->except(['show', 'create']);

    Route::resource('taxes', TaxController::class)->except('show', 'create', 'edit');

    Route::get('taxes/list', [TaxController::class, 'list'])
        ->name('taxes.list');

    Route::resource('currencies', CurrencyRateController::class)->except('show', 'create', 'edit');

    Route::get('currencies/list', [CurrencyRateController::class, 'list'])
        ->name('currencies.list');

    Route::post('currencies/update-currency-rates', [CurrencyRateController::class, 'updateCurrencyRate'])
        ->name('currencies.update-rates');

    Route::resource('special-do-charges', SpecialDOChargeController::class)->except('show', 'edit');

    Route::get('special-do-charges/list', [SpecialDOChargeController::class, 'list'])
        ->name('special-do-charges.list');

    // Invoice Settings
    Route::post('invoice/settings', [SettingController::class, 'updateInvoiceSettings'])
        ->name('invoice.update');

    // Shipper and Consignee Settings
    Route::get('shipper-consignees', [OfficerController::class, 'index'])
        ->name('shipper-consignees.index');
    Route::post('shipper-consignees', [OfficerController::class, 'store'])
        ->name('shipper-consignees.store');
    Route::get('shipper-consignees/{id}/edit', [OfficerController::class, 'edit'])
        ->name('shipper-consignees.edit');
    Route::put('shipper-consignees/{id}', [OfficerController::class, 'update'])
        ->name('shipper-consignees.update');
    Route::delete('shipper-consignees/{id}', [OfficerController::class, 'destroy'])
        ->name('shipper-consignees.destroy');
});
