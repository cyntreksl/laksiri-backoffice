<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::resource('users', UserController::class)
    ->except(['create', 'show']);

Route::put('users/{user}/password/change', [UserController::class, 'changePassword'])
    ->name('users.password.change');

Route::put('users/{user}/branch/change', [UserController::class, 'changeBranch'])
    ->name('users.branch.update');

Route::get('user-list', [UserController::class, 'list']);

Route::get('users/export', [UserController::class, 'export'])->name('users.export');

Route::post('switch-branch', [UserController::class, 'switchBranch']);

Route::name('users.')->group(function () {
    Route::resource('drivers', DriverController::class);

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');

    Route::get('driver-list', [DriverController::class, 'list']);

    Route::get('customer-list', [CustomerController::class, 'list']);

    Route::put('drivers/{user}/change', [DriverController::class, 'changeDriverBasicDetails'])
        ->name('driver.update');

    Route::put('drivers/{user}/password/change', [DriverController::class, 'changeDriverPassword'])
        ->name('driver.password.update');

    Route::get('drivers/list/export', [DriverController::class, 'export']);

    //Driver Tracking
    Route::get('driver-tracings', function () {
        return Inertia::render('User/DriverTracking');
    })->name('driver-tracings.index');

    // Roles
    Route::resource('roles', RoleController::class);
    Route::get('/permissions/{groupName}', [RoleController::class, 'getPermissionByGroupName']);
});
