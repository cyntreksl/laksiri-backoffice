<?php

use App\Http\Controllers\HBLController;
use Illuminate\Support\Facades\Route;

Route::resource('hbls', HBLController::class);

Route::get('hbl-list', [HBLController::class, 'list']);

Route::get('hbls/show/draft-list', [HBLController::class, 'showDraftList'])
    ->name('hbls.draft-list');

Route::get('hbl-draft-list', [HBLController::class, 'getDraftList']);

Route::get('hbls/show/door-to-door', [HBLController::class, 'showDoorToDoorList'])
    ->name('hbls.door-to-door-list');

Route::get('hbl-door-to-door-list', [HBLController::class, 'getDoorToDoorList']);

Route::put('hbls/toggle-hold/{hbl}', [HBLController::class, 'toggleHold'])
    ->name('hbls.toggle-hold');

Route::get('hbls/download/{hbl}', [HBLController::class, 'downloadHBLPDF'])
    ->name('hbls.download');

Route::get('hbls/cancelled-hbls/download/{hbl}', [HBLController::class, 'downloadCancelledHBLPDF'])
    ->name('hbls.cancelled-hbls.download');

Route::get('hbls/download/receipt/{hbl}', [HBLController::class, 'getCashierReceipt'])
    ->name('hbls.getCashierReceipt');

Route::get('hbls/show/cancelled-hbls', [HBLController::class, 'cancelledHBLs'])
    ->name('hbls.cancelled-hbls');

Route::get('hbl-cancelled-list', [HBLController::class, 'cancelledList']);

Route::get('hbls/{hbl}/restore', [HBLController::class, 'restore'])
    ->name('hbls.restore');

Route::get('hbls/download/invoice/{hbl}', [HBLController::class, 'downloadHBLInvoicePDF'])
    ->name('hbls.download.invoice');

Route::get('hbls/download/barcode/{hbl}', [HBLController::class, 'downloadHBLBarcodePDF'])
    ->name('hbls.download.barcode');

Route::get('get-hbl/{package_id}', [HBLController::class, 'getHBLByPackageId']);

Route::post('hbls/uploads/document', [HBLController::class, 'uploadDocument'])
    ->name('hbls.upload.document');

Route::get('hbls/get-hbl-documents/{hbl}', [HBLController::class, 'getHBLDocuments']);

Route::delete('hbls-documents/{hbl_document}', [HBLController::class, 'destroyHBLDocument'])
    ->name('hbls.destroy.document');

Route::get('get-pickup-status/{id}', [HBLController::class, 'getPickupStatus']);

Route::get('get-hbl-status/{hbl}', [HBLController::class, 'getHBLStatus']);

Route::get('hbls/list/export', [HBLController::class, 'export']);

Route::get('hbls/cancelled/list/export', [HBLController::class, 'exportCancelled']);

Route::get('/get-hbl-by-reference/{reference}', [HBLController::class, 'getHBLByReference']);

Route::get('/get-hbl-packages-by-reference/{reference}', [HBLController::class, 'getHBLPackagesByReference']);

Route::get('/get-logs/{hbl}', [HBLController::class, 'getHBLLogs']);

Route::get('/create-token/{hbl}', [HBLController::class, 'createToken'])
    ->name('hbls.create-token');

Route::get('hbls/get-hbls-by-user/{user}', [HBLController::class, 'getHBLsByUser'])
    ->name('hbls.get-hbls-by-user');

Route::get('hbls/download/document/{hbl_document}', [HBLController::class, 'downloadDocument'])
    ->name('hbls.download.document-hbl-document');

Route::post('hbls/calculate-payment', [HBLController::class, 'calculatePayment']);

Route::post('hbls/create-call-flag/{hbl}', [HBLController::class, 'createCallFlag'])
    ->name('hbls.create-call-flag');

Route::get('/get-call-flags/{hbl}', [HBLController::class, 'getHBLCallFlags']);

Route::post('/get-hbl-packages', [HBLController::class, 'getHBLPackageRules']);

Route::post('/get-hbl-rules', [HBLController::class, 'getHBLRules'])->name('hbls.rules');

Route::get('hbls/download/baggage/{hbl}', [HBLController::class, 'downloadBaggagePDF'])
    ->name('hbls.download.baggage');
