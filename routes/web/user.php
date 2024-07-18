<?php

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

Route::post('switch-branch', [UserController::class, 'switchBranch']);

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
