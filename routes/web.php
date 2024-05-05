<?php

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
    // HBL
    Route::resource('hbls', HBLController::class);
    // User
    Route::resource('users', UserController::class)
        ->except(['create']);

    Route::put('users/{user}/password/change', [UserController::class, 'changePassword'])
        ->name('users.password.change');

    Route::put('users/{user}/branch/change', [UserController::class, 'changeBranch'])
        ->name('users.branch.update');

    Route::get('user-list', [UserController::class, 'list']);

    Route::post('switch-branch', [UserController::class, 'switchBranch']);
});
