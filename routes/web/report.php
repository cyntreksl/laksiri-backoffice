<?php

use App\Http\Controllers\AgentWiseContainerArrivalSummaryController;
use App\Http\Controllers\AgentWiseConsigneeVolumeAnalysisController;
use App\Http\Controllers\AgentWiseIncomeAnalysisController;
use App\Http\Controllers\BondStorageRecordsController;
use App\Http\Controllers\ConsigneeClearanceDetailsController;
use App\Http\Controllers\DailyCollectionReportController;
use App\Http\Controllers\DetailInvoiceAnalysisController;
use App\Http\Controllers\ContainerWiseIncomeAnalysisController;
use App\Http\Controllers\DetainReportController;
use App\Http\Controllers\FreightChargesReportController;
use App\Http\Controllers\HBLReportController;
use App\Http\Controllers\HBLPackageReportController;
use App\Http\Controllers\ManifestListingReportController;
use App\Http\Controllers\ManifestClearanceStatusController;
use App\Http\Controllers\ShipmentReportController;
use App\Http\Controllers\ShortLandReportController;
use App\Http\Controllers\OverLandReportController;
use App\Http\Controllers\AgeAnalysisConsigneeController;
use App\Http\Controllers\StampDutyReportController;
use App\Http\Controllers\UnclearedRTFConsigneeDetailsController;
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

    // Manifest Listing Report
    Route::get('manifest-listing', [ManifestListingReportController::class, 'index'])
        ->name('manifest-listing.index')
        ->middleware('can:reports.manifest-listing');

    Route::get('manifest-listing/data', [ManifestListingReportController::class, 'getData'])
        ->name('manifest-listing.data')
        ->middleware('can:reports.manifest-listing');

    Route::get('manifest-listing/export', [ManifestListingReportController::class, 'export'])
        ->name('manifest-listing.export')
        ->middleware('can:reports.manifest-listing');

    // Manifest Clearance Status Report
    Route::get('manifest-clearance-status', [ManifestClearanceStatusController::class, 'index'])
        ->name('manifest-clearance-status.index')
        ->middleware('can:reports.manifest-clearance-status');

    Route::get('manifest-clearance-status/data', [ManifestClearanceStatusController::class, 'getData'])
        ->name('manifest-clearance-status.data')
        ->middleware('can:reports.manifest-clearance-status');

    Route::get('manifest-clearance-status/export', [ManifestClearanceStatusController::class, 'export'])
        ->name('manifest-clearance-status.export')
        ->middleware('can:reports.manifest-clearance-status');

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

    // Container Wise Income Analysis Report
    Route::get('container-wise-income', [ContainerWiseIncomeAnalysisController::class, 'index'])
        ->name('container-wise-income.index')
        ->middleware('can:reports.container-wise-income');

    Route::get('container-wise-income/data', [ContainerWiseIncomeAnalysisController::class, 'getData'])
        ->name('container-wise-income.data')
        ->middleware('can:reports.container-wise-income');

    Route::get('container-wise-income/export', [ContainerWiseIncomeAnalysisController::class, 'export'])
        ->name('container-wise-income.export')
        ->middleware('can:reports.container-wise-income');

    // Uncleared RTF Consignee Details Report
    Route::get('uncleared-rtf-consignee', [UnclearedRTFConsigneeDetailsController::class, 'index'])
        ->name('uncleared-rtf-consignee.index')
        ->middleware('can:reports.uncleared-rtf-consignee');

    Route::get('uncleared-rtf-consignee/data', [UnclearedRTFConsigneeDetailsController::class, 'getData'])
        ->name('uncleared-rtf-consignee.data')
        ->middleware('can:reports.uncleared-rtf-consignee');

    Route::get('uncleared-rtf-consignee/export', [UnclearedRTFConsigneeDetailsController::class, 'export'])
        ->name('uncleared-rtf-consignee.export')
        ->middleware('can:reports.uncleared-rtf-consignee');

    // Agent Wise Income Analysis Report
    Route::get('agent-wise-income', [AgentWiseIncomeAnalysisController::class, 'index'])
        ->name('agent-wise-income.index')
        ->middleware('can:reports.agent-wise-income');

    Route::get('agent-wise-income/data', [AgentWiseIncomeAnalysisController::class, 'getData'])
        ->name('agent-wise-income.data')
        ->middleware('can:reports.agent-wise-income');

    Route::get('agent-wise-income/export', [AgentWiseIncomeAnalysisController::class, 'export'])
        ->name('agent-wise-income.export')
        ->middleware('can:reports.agent-wise-income');

    // Agent Wise Consignee & Volume Analysis Report
    Route::get('agent-wise-consignee-volume', [AgentWiseConsigneeVolumeAnalysisController::class, 'index'])
        ->name('agent-wise-consignee-volume.index')
        ->middleware('can:reports.agent-wise-consignee-volume');

    Route::get('agent-wise-consignee-volume/data', [AgentWiseConsigneeVolumeAnalysisController::class, 'getData'])
        ->name('agent-wise-consignee-volume.data')
        ->middleware('can:reports.agent-wise-consignee-volume');

    Route::get('agent-wise-consignee-volume/export', [AgentWiseConsigneeVolumeAnalysisController::class, 'export'])
        ->name('agent-wise-consignee-volume.export')
        ->middleware('can:reports.agent-wise-consignee-volume');

    // Consignee Clearance Details Report
    Route::get('consignee-clearance', [ConsigneeClearanceDetailsController::class, 'index'])
        ->name('consignee-clearance.index')
        ->middleware('can:reports.consignee-clearance');

    Route::get('consignee-clearance/data', [ConsigneeClearanceDetailsController::class, 'getData'])
        ->name('consignee-clearance.data')
        ->middleware('can:reports.consignee-clearance');

    Route::get('consignee-clearance/export', [ConsigneeClearanceDetailsController::class, 'export'])
        ->name('consignee-clearance.export')
        ->middleware('can:reports.consignee-clearance');

    // Short Land Report
    Route::get('short-land', [ShortLandReportController::class, 'index'])
        ->name('short-land.index')
        ->middleware('can:reports.short-land');

    Route::get('short-land/data', [ShortLandReportController::class, 'getData'])
        ->name('short-land.data')
        ->middleware('can:reports.short-land');

    Route::get('short-land/export', [ShortLandReportController::class, 'export'])
        ->name('short-land.export')
        ->middleware('can:reports.short-land');

    // Over Land Report
    Route::get('over-land', [OverLandReportController::class, 'index'])
        ->name('over-land.index')
        ->middleware('can:reports.over-land');

    Route::get('over-land/data', [OverLandReportController::class, 'getData'])
        ->name('over-land.data')
        ->middleware('can:reports.over-land');

    Route::get('over-land/export', [OverLandReportController::class, 'export'])
        ->name('over-land.export')
        ->middleware('can:reports.over-land');

    Route::get('over-land/debug', [OverLandReportController::class, 'debug'])
        ->name('over-land.debug')
        ->middleware('can:reports.over-land');

    // Age Analysis Consignee Report
    Route::get('age-analysis-consignee', [AgeAnalysisConsigneeController::class, 'index'])
        ->name('age-analysis-consignee.index')
        ->middleware('can:reports.age-analysis-consignee');

    Route::get('age-analysis-consignee/data', [AgeAnalysisConsigneeController::class, 'getData'])
        ->name('age-analysis-consignee.data')
        ->middleware('can:reports.age-analysis-consignee');

    Route::get('age-analysis-consignee/export', [AgeAnalysisConsigneeController::class, 'export'])
        ->name('age-analysis-consignee.export')
        ->middleware('can:reports.age-analysis-consignee');

    Route::get('age-analysis-consignee/containers', [AgeAnalysisConsigneeController::class, 'getContainers'])
        ->name('age-analysis-consignee.containers')
        ->middleware('can:reports.age-analysis-consignee');

    // Bond Storage Records Report
    Route::get('bond-storage-records', [BondStorageRecordsController::class, 'index'])
        ->name('bond-storage-records.index')
        ->middleware('can:reports.bond-storage-records');

    Route::get('bond-storage-records/data', [BondStorageRecordsController::class, 'getData'])
        ->name('bond-storage-records.data')
        ->middleware('can:reports.bond-storage-records');

    Route::get('bond-storage-records/export', [BondStorageRecordsController::class, 'export'])
        ->name('bond-storage-records.export')
        ->middleware('can:reports.bond-storage-records');

    // Unmanifested Cargo Report
    Route::get('unmanifested-cargo', [\App\Http\Controllers\UnmanifestedCargoController::class, 'index'])
        ->name('unmanifested-cargo.index')
        ->middleware('can:reports.unmanifested-cargo');

    Route::get('unmanifested-cargo/data', [\App\Http\Controllers\UnmanifestedCargoController::class, 'getData'])
        ->name('unmanifested-cargo.data')
        ->middleware('can:reports.unmanifested-cargo');

    Route::get('unmanifested-cargo/export', [\App\Http\Controllers\UnmanifestedCargoController::class, 'export'])
        ->name('unmanifested-cargo.export')
        ->middleware('can:reports.unmanifested-cargo');

    // Letter Registration Records Report
    Route::get('letter-registration-records', [\App\Http\Controllers\LetterRegistrationRecordsController::class, 'index'])
        ->name('letter-registration-records.index')
        ->middleware('can:reports.letter-registration-records');

    Route::get('letter-registration-records/data', [\App\Http\Controllers\LetterRegistrationRecordsController::class, 'getData'])
        ->name('letter-registration-records.data')
        ->middleware('can:reports.letter-registration-records');

    Route::get('letter-registration-records/export', [\App\Http\Controllers\LetterRegistrationRecordsController::class, 'export'])
        ->name('letter-registration-records.export')
        ->middleware('can:reports.letter-registration-records');
});
