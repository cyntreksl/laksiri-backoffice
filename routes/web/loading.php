<?php

use App\Http\Controllers\ContainerController;
use App\Http\Controllers\LoadedContainerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::name('loading.')->group(function () {
    // Containers
    Route::resource('containers', ContainerController::class)->names([
        'index' => 'loading-containers.index',
        'create' => 'loading-containers.create',
        'store' => 'loading-containers.store',
        'edit' => 'loading-containers.edit',
        'update' => 'loading-containers.update',
        'destroy' => 'loading-containers.destroy',
    ]);

    Route::get('container-list', [ContainerController::class, 'list']);

    Route::post('containers/uploads/document', [ContainerController::class, 'uploadDocument'])
        ->name('containers.upload.document');

    Route::get('containers/get-container-documents/{container}', [ContainerController::class, 'getContainerDocuments']);

    Route::delete('containers-documents/{container_document}', [ContainerController::class, 'destroyContainerDocument'])
        ->name('containers.destroy.document');

    Route::get('/get-container/{hbl}', [ContainerController::class, 'getContainerByHBL']);

    Route::get('containers-documents/{container_document}', [ContainerController::class, 'downloadDocument'])
        ->name('containers-documents.download');

    Route::get('containers/get-container-by-reference/{reference}', [ContainerController::class, 'getContainerByReference']);

    // Loading Point
    Route::get('loading-points/{container}', [ContainerController::class, 'showLoadingPoint'])
        ->name('loading-points.index');

    Route::get('containers/hbl/batch-downloads/{container}', [ContainerController::class, 'batchDownloadPDF'])
        ->name('hbls.batch-downloads');

    Route::get('hbls/get-unloaded-hbl/list', [ContainerController::class, 'getUnloadedHBLs']);

    Route::get('hbls/get-destination-unloaded-hbl/list', [ContainerController::class, 'getDestinationUnloadedHBLs']);

    Route::get('containers/list/export', [ContainerController::class, 'export']);

    // Loaded Container
    Route::resource('loaded-containers', LoadedContainerController::class)
        ->except(['create']);

    Route::post('/loaded-containers/remove', [LoadedContainerController::class, 'destroyDraft'])
        ->name('loaded-containers.remove');

    Route::post('/loaded-containers/add-mhbl', [LoadedContainerController::class, 'loadMHBL'])
        ->name('loaded-containers.add-mhbl');

    Route::get('loaded-container-list', [LoadedContainerController::class, 'list']);

    Route::get('/loaded-containers/{container}/manifest/export', [LoadedContainerController::class, 'exportManifest'])
        ->name('loaded-containers.manifest.export');

    Route::get('/loaded-containers/{container}/doorToDoor/export', [LoadedContainerController::class, 'doorToDoorManifest'])
        ->name('loaded-containers.doorToDoor.export');

    Route::get('loaded-containers/download-loading/{container}', [LoadedContainerController::class, 'downloadLoadingPointDoc'])
        ->name('loaded-containers.download-loading');

    Route::get('loaded-containers/get-container/{id}', [LoadedContainerController::class, 'getLoadedContainer'])
        ->name('loaded-containers.single-container');

    Route::put('containers/{container}/unload/hbl', [ContainerController::class, 'unloadHBLFromContainer'])
        ->name('containers.unload.hbl');

    Route::put('containers/{container}/unload/mhbl', [ContainerController::class, 'unloadMHBLFromContainer'])
        ->name('containers.unload.mhbl');

    Route::post('containers/get-hbl/packages', [ContainerController::class, 'getHBLWithUnloadedPackages'])
        ->name('containers.get-hbl-with-packages');

    Route::put('containers/{container}/delete-loading', [ContainerController::class, 'deleteLoading'])
        ->name('containers.delete-loading');

    Route::get('loaded-containers/list/export', [ContainerController::class, 'exportLoadedShipments']);

    Route::post('loaded-containers/verify', [LoadedContainerController::class, 'verifyDocument'])
        ->name('loaded-containers.verify');

    Route::post('containers/get-unloaded-mhbl-hbl', [ContainerController::class, 'getUnloadedMHBLHBL'])
        ->name('get-unloaded-mhbls-hbl');

    Route::get('containers/tally-sheet-downloads/{container}', [LoadedContainerController::class, 'tallySheetDownloadPDF'])
        ->name('containers.tally-sheet-downloads');

    Route::get('containers/tally-sheet-excel-downloads/{container}', [LoadedContainerController::class, 'tallySheetDownloadExcel'])
        ->name('containers.tally-sheet-excel-downloads');

    Route::post('containers/{container}/set/rtf', [ContainerController::class, 'setRTF'])
        ->name('containers.set.rtf');

    Route::post('containers/{container}/unset/rtf', [ContainerController::class, 'unsetRTF'])
        ->name('containers.unset.rtf');

    Route::get('all-shipments', [ContainerController::class, 'allShipments'])
        ->name('all-shipments');

    Route::get('all-shipments-list', [ContainerController::class, 'allShipmentsList']);

    Route::post('/containers/{container}/remarks', [ContainerController::class, 'storeRemark'])
        ->name('containers.remarks.store');

    // Manual Loadings
    Route::get('manual-loadings', function () {
        return Inertia::render('Loading/ManualLoading');
    })->name('manual-loadings.index');
});
