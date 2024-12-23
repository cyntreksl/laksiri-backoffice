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

    // Loading Point
    Route::get('loading-points/{container}', [ContainerController::class, 'showLoadingPoint'])
        ->name('loading-points.index');

    Route::get('containers/hbl/batch-downloads/{container}', [ContainerController::class, 'batchDownloadPDF'])
        ->name('hbls.batch-downloads');

    Route::get('hbls/get-unloaded-hbl/list', [ContainerController::class, 'getUnloadedHBLs']);

    Route::get('containers/list/export', [ContainerController::class, 'export']);

    // Loaded Container
    Route::resource('loaded-containers', LoadedContainerController::class)
        ->except(['create']);

    Route::post('/loaded-containers/remove', [LoadedContainerController::class, 'destroyDraft'])
        ->name('loaded-containers.remove');

    Route::get('loaded-container-list', [LoadedContainerController::class, 'list']);

    Route::get('/loaded-containers/{container}/manifest/export', [LoadedContainerController::class, 'exportManifest'])
        ->name('loaded-containers.manifest.export');

    Route::get('/loaded-containers/{container}/doorToDoor/export', [LoadedContainerController::class, 'doorToDoorManifest'])
        ->name('loaded-containers.doorToDoor.export');

    Route::put('containers/{container}/unload/hbl', [ContainerController::class, 'unloadHBLFromContainer'])
        ->name('containers.unload.hbl');

    Route::post('containers/get-hbl/packages', [ContainerController::class, 'getHBLWithUnloadedPackages'])
        ->name('containers.get-hbl-with-packages');

    Route::put('containers/{container}/delete-loading', [ContainerController::class, 'deleteLoading'])
        ->name('containers.delete-loading');

    Route::get('loaded-containers/list/export', [ContainerController::class, 'exportLoadedShipments']);

    Route::post('loaded-containers/verify', [LoadedContainerController::class, 'verifyDocument'])
        ->name('loaded-containers.verify');

    // Manual Loadings
    Route::get('manual-loadings', function () {
        return Inertia::render('Loading/ManualLoading');
    })->name('manual-loadings.index');
});
