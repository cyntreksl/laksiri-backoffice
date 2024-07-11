<?php

use App\Http\Controllers\BondedWarehouseController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CashSettlementController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\DriverAreasController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\HBLController;
use App\Http\Controllers\LoadedContainerController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\PickupExceptionController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UnloadingIssueController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseZoneController;
use App\Http\Controllers\ZoneController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    Route::get('/dashboard-2', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard2');

    // Pick Up
    Route::resource('pickups', PickupController::class)
        ->except('edit');

    Route::get('pickup-list', [PickupController::class, 'list']);
    Route::get('pickup-list-order', [PickupController::class, 'showPickupOrder'])->name('pickups.ordering');
    Route::put('pickup-list-update-order', [PickupController::class, 'updatePickupOrder'])->name('pickups.update-order');

    Route::post('pickups/driver/assign', [PickupController::class, 'assignDriver'])
        ->name('pickups.driver.assign');

    Route::get('pickups/exceptions/list', [PickupExceptionController::class, 'index'])
        ->name('pickups.exceptions');

    Route::get('pickup-exception-list', [PickupExceptionController::class, 'list']);

    Route::post('pickups/exceptions/driver/assign', [PickupExceptionController::class, 'assignDriver'])
        ->name('pickups.exceptions.driver.assign');

    Route::post('pickups/exceptions/delete', [PickupExceptionController::class, 'deleteExceptions'])
        ->name('pickups.exceptions.delete');

    // HBL
    Route::resource('hbls', HBLController::class);

    Route::get('hbl-list', [HBLController::class, 'list']);

    Route::put('hbls/toggle-hold/{hbl}', [HBLController::class, 'toggleHold'])
        ->name('hbls.toggle-hold');

    Route::get('hbls/download/{hbl}', [HBLController::class, 'downloadHBLPDF'])
        ->name('hbls.download');

    Route::get('hbls/show/cancelled-hbls', [HBLController::class, 'cancelledHBLs'])
        ->name('hbls.cancelled-hbls');

    Route::get('hbl-cancelled-list', [HBLController::class, 'cancelledList']);

    Route::get('hbls/{hbl}/restore', [HBLController::class, 'restore'])
        ->name('hbls.restore');

    Route::get('hbls/download/invoice/{hbl}', [HBLController::class, 'downloadHBLInvoicePDF'])
        ->name('hbls.download.invoice');

    Route::get('hbls/download/barcode/{hbl}', [HBLController::class, 'downloadHBLBarcodePDF'])
        ->name('hbls.download.barcode');

    Route::get('get-hbl/{package_id}', [HBLController::class, 'getHBLByPackageId']);

    // User
    Route::resource('users', UserController::class)
        ->except(['create', 'show']);

    Route::put('users/{user}/password/change', [UserController::class, 'changePassword'])
        ->name('users.password.change');

    Route::put('users/{user}/branch/change', [UserController::class, 'changeBranch'])
        ->name('users.branch.update');

    Route::get('user-list', [UserController::class, 'list']);

    Route::post('switch-branch', [UserController::class, 'switchBranch']);

    // Driver
    Route::name('users.')->group(function () {
        Route::resource('drivers', DriverController::class);

        Route::get('driver-list', [DriverController::class, 'list']);

        Route::put('drivers/{user}/change', [DriverController::class, 'changeDriverBasicDetails'])
            ->name('driver.update');

        Route::put('drivers/{user}/password/change', [DriverController::class, 'changeDriverPassword'])
            ->name('driver.password.update');

        //Driver Tracking
        Route::get('driver-tracings', function () {
            return Inertia::render('User/DriverTracking');
        })->name('driver-tracings.index');

        // Roles
        Route::resource('roles', RoleController::class);
        Route::get('/permissions/{groupName}', [RoleController::class, 'getPermissionByGroupName']);
    });

    // Back Office
    Route::name('back-office.')->group(function () {
        // Cash Settlements
        Route::get('cash-settlements', [CashSettlementController::class, 'index'])->name('cash-settlements.index');
        Route::get('cash-settlement-list', [CashSettlementController::class, 'list'])->name('cash-settlements.list');
        Route::post('cash-settlement-summery', [CashSettlementController::class, 'getSummery'])->name('cash-settlements.summery');
        Route::post('cash-received', [CashSettlementController::class, 'cashReceived'])->name('cash-settlements.cashReceived');
        Route::put('update/payments/{hbl}', [CashSettlementController::class, 'paymentUpdate'])->name('cash-settlements.payment.update');

        // Warehouse
        Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('get-warehouse-list', [WarehouseController::class, 'list'])->name('warehouses.list');
        Route::post('warehouse-summery', [WarehouseController::class, 'getSummery'])->name('warehouses.summery');
        Route::put('warehouses/{hbl}/assign-zones', [WarehouseController::class, 'assignZone'])->name('warehouses.assign.zone');
    });

    //Loading
    Route::name('loading.')->group(function () {
        // Containers
        Route::resource('containers', ContainerController::class)->names([
            'index' => 'loading-containers.index',
            'create' => 'loading-containers.create',
            'store' => 'loading-containers.store',
            'edit' => 'loading-containers.edit',
            'update' => 'loading-containers.update',
            'destroy' => 'loading-containers.destroy',
        ]);

        Route::get('container-list', [ContainerController::class, 'list']);

        // Loading Point
        Route::get('loading-points/{container}', [ContainerController::class, 'showLoadingPoint'])
            ->name('loading-points.index');

        Route::get('containers/hbl/batch-downloads/{container}', [ContainerController::class, 'batchDownloadPDF'])
            ->name('hbls.batch-downloads');

        Route::get('hbls/get-unloaded-hbl/list', [ContainerController::class, 'getUnloadedHBLs']);

        // Loaded Container
        Route::resource('loaded-containers', LoadedContainerController::class)
            ->except(['create']);

        Route::post('/loaded-containers/remove', [LoadedContainerController::class, 'destroyDraft'])
            ->name('loaded-containers.remove');

        Route::get('loaded-container-list', [LoadedContainerController::class, 'list']);

        Route::get('/loaded-containers/{container}/manifest/export', [LoadedContainerController::class, 'exportManifest'])
            ->name('loaded-containers.manifest.export');

        Route::put('containers/{container}/unload/hbl', [ContainerController::class, 'unloadHBLFromContainer'])
            ->name('containers.unload.hbl');

        Route::post('containers/get-hbl/packages', [ContainerController::class, 'getHBLWithUnloadedPackages'])
            ->name('containers.get-hbl-with-packages');

        Route::put('containers/{container}/delete-loading', [ContainerController::class, 'deleteLoading'])
            ->name('containers.delete-loading');

        // Manual Loadings
        Route::get('manual-loadings', function () {
            return Inertia::render('Loading/ManualLoading');
        })->name('manual-loadings.index');
    });

    //Arrivals
    Route::name('arrival.')->group(function () {
        // Shipments Arrivals
        Route::get('shipments-arrivals', [ContainerController::class, 'showShipmentArrivals'])->name('shipments-arrivals.index');

        Route::get('unloading-points/{container?}', [ContainerController::class, 'showUnloadingPoint'])
            ->name('unloading-points.index');

        Route::post('/unload-container/unload', [ContainerController::class, 'unloadContainer'])
            ->name('unload-container.unload');

        Route::post('/unload-container/reload', [ContainerController::class, 'reloadContainer'])
            ->name('unload-container.reload');

        Route::post('/unloading-points/create/unloading-issue', [ContainerController::class, 'storeUnloadingIssue'])
            ->name('unloading-points.create.unloading-issue');

        // Bonded Warehouse
        Route::get('bonded-warehouses', [BondedWarehouseController::class, 'index'])
            ->name('bonded-warehouses.index');

        Route::get('bonded-warehouse-list', [BondedWarehouseController::class, 'list']);

        Route::get('hbl/mark-as-short-loading/{hbl_id}', [BondedWarehouseController::class, 'markAsShortLoading'])
            ->name('hbls.mark-as-short-loading');

        //Unloading Issues
        Route::get('unloading-issues', [UnloadingIssueController::class, 'index'])
            ->name('unloading-issues.index');

        Route::get('unloading-issues-list', [UnloadingIssueController::class, 'list']);
    });

    //Delivery
    Route::name('delivery.')->group(function () {
        //Delivery Warehouse
        Route::get('delivery-warehouses', function () {
            return Inertia::render('Delivery/DeliveryWarehouseList');
        })->name('delivery-warehouses.index');

        // Dispatch Point
        Route::get('dispatch-points', function () {
            return Inertia::render('Delivery/DispatchPointList');
        })->name('dispatch-points.index');

        //Dispatched Loads
        Route::get('dispatched-loads', function () {
            return Inertia::render('Delivery/DispatchedLoadList');
        })->name('dispatched-loads.index');
    });

    //Reports
    Route::name('report.')->group(function () {
        //Payment Summery
        Route::get('payment-summaries', function () {
            return Inertia::render('Report/PaymentSummeryList');
        })->name('payment-summaries.index');

    });

    //Setting
    Route::name('setting.')->group(function () {
        // Zones
        Route::get('zones/list', [ZoneController::class, 'list'])->name('driver-zones.list');
        Route::resource('zones', ZoneController::class)
            ->except(['create', 'show'])->name('index', 'driver-zones.index');

        //Driver Areas
        Route::resource('driver-areas', DriverAreasController::class)->except('show');
        Route::get('driver-areas/list', [DriverAreasController::class, 'list'])->name('driver-area.list');

        //Warehouse Zones
        Route::get('warehouse-zones/list', [WarehouseZoneController::class, 'list'])->name('warehouse-zones.list');
        Route::get('warehouse-zones', [WarehouseZoneController::class, 'index'])->name('warehouse-zones.index');
        Route::delete('warehouse-zones/{id}', [WarehouseZoneController::class, 'delete'])->name('warehouse-zones.delete');
        Route::post('warehousezones/create', [WarehouseZoneController::class, 'store'])->name('warehouse-zones.store');
        Route::post('warehousezones/update', [WarehouseZoneController::class, 'update'])->name('warehouse-zones.update');
        Route::get('warehousezones/{id}/edit', [WarehouseZoneController::class, 'edit'])->name('warehouse-zones.edit');

        //Pricing
        Route::resource('prices', PriceController::class)->except('show');
    });

    // Branches
    Route::resource('branches', BranchController::class)->except('show');
});
