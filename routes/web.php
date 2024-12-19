<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HBLController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    \Illuminate\Support\Facades\Log::error('sever test');

    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

require_once __DIR__.'/web/feedback.php';

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/call-center/dashboard', [\App\Http\Controllers\CallCenter\DashboardController::class, 'index'])
        ->name('call-center.dashboard');

    require_once __DIR__.'/web/arrival.php';
    require_once __DIR__.'/web/back-office.php';
    require_once __DIR__.'/web/branch.php';
    require_once __DIR__.'/web/delivery.php';
    require_once __DIR__.'/web/hbl.php';
    require_once __DIR__.'/web/mhbl.php';
    require_once __DIR__.'/web/loading.php';
    require_once __DIR__.'/web/pickup.php';
    require_once __DIR__.'/web/report.php';
    require_once __DIR__.'/web/setting.php';
    require_once __DIR__.'/web/user.php';
    require_once __DIR__.'/web/file-manager.php';

    // call center routes
    Route::name('call-center.')->prefix('call-center')->group(function () {
        require_once __DIR__.'/web/call-center/hbl.php';
        require_once __DIR__.'/web/call-center/queue.php';
        require_once __DIR__.'/web/call-center/verification.php';
        require_once __DIR__.'/web/call-center/cashier.php';
        require_once __DIR__.'/web/call-center/examination.php';
        require_once __DIR__.'/web/call-center/boned-area.php';
    });
});

Route::get('get-hbl-status-by-reference/{reference}', [HBLController::class, 'getHBLStatusByReference']);
