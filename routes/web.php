<?php

use App\Http\Controllers\DriverController;
use App\Http\Controllers\HBLController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\UserController;
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
    Route::resource('pickups', PickupController::class);
    Route::put('pickups/{pickup}/driver/update', [PickupController::class, 'updateDriver'])
        ->name('pickups.driver.update');
    // HBL
    Route::resource('hbls', HBLController::class);
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
    });

    // Back Office
    Route::name('back-office.')->group(function () {
        // Cash Settlements
        Route::get('cash-settlements', function () {
            return Inertia::render('CashSettlement/CashSettlementList');
        })->name('cash-settlements.index');

        // Warehouse
        Route::get('warehouses', function () {
            return Inertia::render('Warehouse/WarehouseList');
        })->name('warehouses.index');
    });

    //Loading
    Route::name('loading.')->group(function () {
        // Loading Point
        Route::get('loading-points', function () {
            return Inertia::render('Loading/LoadingPoint');
        })->name('loading-points.index');

        // Warehouse
        Route::get('manual-loadings', function () {
            return Inertia::render('Loading/ManualLoading');
        })->name('manual-loadings.index');

        //Loaded Shipments
        Route::get('loaded-shipments', function () {
            return Inertia::render('Loading/LoadedShipmentList');
        })->name('loaded-shipments.index');
    });

    //Arrivals
    Route::name('arrival.')->group(function () {
        // Shipments Arrivals
        Route::get('shipments-arrivals', function () {
            return Inertia::render('Arrival/ShipmentsArrivalsList');
        })->name('shipments-arrivals.index');

        // Bonded Warehouse
        Route::get('bonded-warehouses', function () {
            return Inertia::render('Arrival/BondedWarehouseList');
        })->name('bonded-warehouses.index');

        //Unloading Issues
        Route::get('unloading-issues', function () {
            return Inertia::render('Arrival/UnloadingIssueList');
        })->name('unloading-issues.index');
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



});
