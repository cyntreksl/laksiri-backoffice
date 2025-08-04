<?php

use App\Http\Controllers\WhatsappController;
use Illuminate\Support\Facades\Route;

Route::name('whatsapp.')->prefix('whatsapp')->group(function () {
    Route::get('/messaging', [WhatsappController::class, 'index'])
        ->name('index');

    Route::post('/contacts', [WhatsappController::class, 'storeContact'])
        ->name('contacts.store');

    // Send message endpoint
    Route::post('/send', [WhatsappController::class, 'sendMessage'])
        ->name('send');
});

// Webhook endpoints (these should be accessible without authentication)
Route::name('whatsapp.webhook.')->prefix('whatsapp')->group(function () {
    Route::post('/webhook', [WhatsappController::class, 'handleWebhook'])
        ->name('handle')
        ->withoutMiddleware(['auth:sanctum']);

    Route::get('/webhook', [WhatsappController::class, 'verifyWebhook'])
        ->name('verify')
        ->withoutMiddleware(['auth:sanctum']);
});
