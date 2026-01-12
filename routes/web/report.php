<?php

use App\Http\Controllers\DetainReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DetainReportExport;

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
});
