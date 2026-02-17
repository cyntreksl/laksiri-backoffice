<?php

use App\Http\Controllers\DetainReportController;
use App\Http\Controllers\HBLReportController;
use App\Http\Controllers\HBLPackageReportController;
use App\Http\Controllers\ShipmentReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DetainReportExport;
use App\Exports\HBLReportExport;

Route::name('report.')->group(function () {
    // Payment Summery
    Route::get('payment-summaries', function () {
        return Inertia::render('Report/PaymentSummeryList');
    })->name('payment-summaries.index');

    // Detain Report
    Route::get('detain-report', [DetainReportController::class, 'index'])
        ->name('detain-report.index');

    Route::get('detain-report/data', [DetainReportController::class, 'getData'])
        ->name('detain-report.data');

    Route::get('detain-report/export', function () {
        return Excel::download(new DetainReportExport(request()), 'detain-report-' . date('Y-m-d-His') . '.xlsx');
    })->name('detain-report.export');

    // HBL Report
    Route::get('hbl-report', [HBLReportController::class, 'index'])
        ->name('hbl-report.index')
        ->middleware('can:reports.hbl');

    Route::get('hbl-report/data', [HBLReportController::class, 'getData'])
        ->name('hbl-report.data')
        ->middleware('can:reports.hbl');

    Route::get('hbl-report/export', [HBLReportController::class, 'export'])
        ->name('hbl-report.export')
        ->middleware('can:reports.hbl');

    // HBL Package Report
    Route::get('hbl-package-report', [HBLPackageReportController::class, 'index'])
        ->name('hbl-package-report.index')
        ->middleware('can:reports.hbl-package');

    Route::get('hbl-package-report/data', [HBLPackageReportController::class, 'getData'])
        ->name('hbl-package-report.data')
        ->middleware('can:reports.hbl-package');

    Route::get('hbl-package-report/export', [HBLPackageReportController::class, 'export'])
        ->name('hbl-package-report.export')
        ->middleware('can:reports.hbl-package');

    Route::get('hbl-package-report/{hbl}/details', [HBLPackageReportController::class, 'getHBLDetails'])
        ->name('hbl-package-report.hbl-details')
        ->middleware('can:reports.hbl-package');

    // Shipment Report
    Route::get('shipment-report', [ShipmentReportController::class, 'index'])
        ->name('shipment-report.index')
        ->middleware('can:reports.shipment');

    Route::get('shipment-report/data', [ShipmentReportController::class, 'getData'])
        ->name('shipment-report.data')
        ->middleware('can:reports.shipment');

    Route::get('shipment-report/export', [ShipmentReportController::class, 'export'])
        ->name('shipment-report.export')
        ->middleware('can:reports.shipment');
});
