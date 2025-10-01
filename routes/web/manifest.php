<?php

use App\Http\Controllers\ManifestImportController;
use App\Models\ExtractedManifestRow;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Manifest Import Routes
|--------------------------------------------------------------------------
|
| Here are the routes for manifest import functionality
|
*/

// Test route to verify functionality
Route::get('/manifest/test', function() {
    try {
        // Test model instantiation
        $model = new ExtractedManifestRow();
        
        // Test controller instantiation
        $controller = new ManifestImportController();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Manifest import functionality is working correctly!',
            'model' => get_class($model),
            'controller' => get_class($controller),
            'table' => $model->getTable(),
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::prefix('manifest')->name('manifest.')->group(function () {
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [ManifestImportController::class, 'index'])->name('index');
        Route::post('/upload', [ManifestImportController::class, 'upload'])->name('upload');
        Route::get('/review', [ManifestImportController::class, 'review'])->name('review');
        Route::post('/confirm', [ManifestImportController::class, 'confirm'])->name('confirm');
        Route::post('/cancel', [ManifestImportController::class, 'cancel'])->name('cancel');
    });
});