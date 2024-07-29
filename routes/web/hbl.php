<?php

use App\Http\Controllers\HBLController;
use Illuminate\Support\Facades\Route;

Route::resource('hbls', HBLController::class);

Route::get('hbl-list', [HBLController::class, 'list']);

Route::put('hbls/toggle-hold/{hbl}', [HBLController::class, 'toggleHold'])
    ->name('hbls.toggle-hold');

Route::get('hbls/download/{hbl}', [HBLController::class, 'downloadHBLPDF'])
    ->name('hbls.download');

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

Route::get('get-pickup-status/{hbl}', [HBLController::class, 'getPickupStatus']);

Route::get('get-hbl-status/{hbl}', [HBLController::class, 'getHBLStatus']);

Route::get('hbls/list/export', [HBLController::class, 'export']);

Route::get('hbls/cancelled/list/export', [HBLController::class, 'exportCancelled']);

Route::get('/get-hbl-by-reference/{reference}', [HBLController::class, 'getHBLByReference']);

Route::get('/get-hbl-packages-by-reference/{reference}', [HBLController::class, 'getHBLPackagesByReference']);
