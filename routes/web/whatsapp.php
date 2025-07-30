<?php

use App\Http\Controllers\WhatsappController;
use Illuminate\Support\Facades\Route;

Route::name('whatsapp.')->prefix('whatsapp/')->group(function () {
    Route::get('messaging', [WhatsappController::class, 'index'])
        ->name('index');
});
