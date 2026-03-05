<?php

use App\Http\Controllers\AgentWiseContainerArrivalSummaryController;
use App\Http\Controllers\DailyCollectionReportController;
use App\Http\Controllers\DetailInvoiceAnalysisController;
use App\Http\Controllers\DetainReportController;
use App\Http\Controllers\FreightChargesReportController;
use App\Http\Controllers\HBLReportController;
use App\Http\Controllers\HBLPackageReportController;
use App\Http\Controllers\ShipmentReportController;
use App\Http\Controllers\StampDutyReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

    Route::get('detain-report/export', [DetainReportController::class, 'export'])
        ->name('detain-report.export');

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

    // Container Arrival Summary
    Route::get('agent-wise-container-arrival-summary', [AgentWiseContainerArrivalSummaryController::class, 'index'])
        ->name('agent-wise-container-arrival-summary.index')
        ->middleware('can:reports.agent-wise-container-arrival');

    Route::get('agent-wise-container-arrival-summary/data', [AgentWiseContainerArrivalSummaryController::class, 'getData'])
        ->name('agent-wise-container-arrival-summary.data')
        ->middleware('can:reports.agent-wise-container-arrival');

    Route::get('agent-wise-container-arrival-summary/export', [AgentWiseContainerArrivalSummaryController::class, 'export'])
        ->name('agent-wise-container-arrival-summary.export')
        ->middleware('can:reports.agent-wise-container-arrival');

    // Daily Collection Report
    Route::get('daily-collection', [DailyCollectionReportController::class, 'index'])
        ->name('daily-collection.index')
        ->middleware('can:reports.daily-collection');

    Route::get('daily-collection/data', [DailyCollectionReportController::class, 'getData'])
        ->name('daily-collection.data')
        ->middleware('can:reports.daily-collection');

    Route::get('daily-collection/export', [DailyCollectionReportController::class, 'export'])
        ->name('daily-collection.export')
        ->middleware('can:reports.daily-collection');

    // Freight Charges Report
    Route::get('freight-charges', [FreightChargesReportController::class, 'index'])
        ->name('freight-charges.index')
        ->middleware('can:reports.freight-charges');

    Route::get('freight-charges/data', [FreightChargesReportController::class, 'getData'])
        ->name('freight-charges.data')
        ->middleware('can:reports.freight-charges');

    Route::get('freight-charges/export', [FreightChargesReportController::class, 'export'])
        ->name('freight-charges.export')
        ->middleware('can:reports.freight-charges');

    // Stamp Duty Report
    Route::get('stamp-duty', [StampDutyReportController::class, 'index'])
        ->name('stamp-duty.index')
        ->middleware('can:reports.stamp-duty');

    Route::get('stamp-duty/data', [StampDutyReportController::class, 'getData'])
        ->name('stamp-duty.data')
        ->middleware('can:reports.stamp-duty');

    Route::get('stamp-duty/export', [StampDutyReportController::class, 'export'])
        ->name('stamp-duty.export')
        ->middleware('can:reports.stamp-duty');

    // Detail Invoice Analysis Report
    Route::get('detail-invoice-analysis', [DetailInvoiceAnalysisController::class, 'index'])
        ->name('detail-invoice-analysis.index')
        ->middleware('can:reports.detail-invoice-analysis');

    Route::get('detail-invoice-analysis/data', [DetailInvoiceAnalysisController::class, 'getData'])
        ->name('detail-invoice-analysis.data')
        ->middleware('can:reports.detail-invoice-analysis');

    Route::get('detail-invoice-analysis/export', [DetailInvoiceAnalysisController::class, 'export'])
        ->name('detail-invoice-analysis.export')
        ->middleware('can:reports.detail-invoice-analysis');
});
