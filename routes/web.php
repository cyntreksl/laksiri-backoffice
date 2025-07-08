<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HBLController;
use App\Http\Controllers\ThirdPartyShipmentController;
use App\Http\Controllers\WhatsappController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/tracking', [HBLController::class, 'showTracking'])
    ->name('tracking.page');

require_once __DIR__.'/web/feedback.php';

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

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
    require_once __DIR__.'/web/courier.php';
    require_once __DIR__.'/web/container-payment.php';
    require_once __DIR__.'/web/gate-control.php';

    // call center routes
    Route::name('call-center.')->prefix('call-center')->group(function () {
        require_once __DIR__.'/web/call-center/hbl.php';
        require_once __DIR__.'/web/call-center/queue.php';
        require_once __DIR__.'/web/call-center/verification.php';
        require_once __DIR__.'/web/call-center/cashier.php';
        require_once __DIR__.'/web/call-center/examination.php';
        require_once __DIR__.'/web/call-center/boned-area.php';
        require_once __DIR__.'/web/call-center/reception.php';
        require_once __DIR__.'/web/call-center/tokens.php';
    });

    // finance routes
    Route::name('finance.')->prefix('finance')->group(function () {
        require_once __DIR__.'/web/finance/hbl.php';
    });

    Route::name('clearance.')->prefix('clearance')->group(function () {
        require_once __DIR__.'/web/clearance/vessel-schedule.php';
    });

    // Third Party Shipment CSV Import Routes (must come before resource routes)
    Route::post('third-party-shipments/import-csv', [ThirdPartyShipmentController::class, 'importCsv'])
        ->name('third-party-shipments.import-csv');

    Route::get('third-party-shipments/get-tmp-hbls', [ThirdPartyShipmentController::class, 'getTmpHbls'])
        ->name('third-party-shipments.get-tmp-hbls');

    Route::post('third-party-shipments/save-shipment', [ThirdPartyShipmentController::class, 'saveShipment'])
        ->name('third-party-shipments.save-shipment');

    Route::get('third-party-shipments/download-sample', [ThirdPartyShipmentController::class, 'downloadSample'])
        ->name('third-party-shipments.download-sample');

    Route::resource('third-party-shipments', ThirdPartyShipmentController::class);

    Route::get('third-party-shipments/multi/options', [ThirdPartyShipmentController::class, 'multiOptions'])
        ->name('third-party-shipments.multi-options');

    Route::get('third-party-shipments/create/v2', [ThirdPartyShipmentController::class, 'createV2'])
        ->name('third-party-shipments.create.v2');

    Route::post('third-party-shipments/save-shipment/v2', [ThirdPartyShipmentController::class, 'saveShipmentV2'])
        ->name('third-party-shipments.save-shipment.v2');

});

Route::get('get-hbl-status-by-reference/{reference}', [HBLController::class, 'getHBLStatusByReference']);

Route::post('/whatsapp/webhook', [WhatsappController::class, 'handleWebhook']);
Route::get('/whatsapp/webhook', [WhatsappController::class, 'verifyWebhook']);

Route::get('/calculate-payments/manually/{hbl_number}', [HBLController::class, 'calculatePaymentManual']);
