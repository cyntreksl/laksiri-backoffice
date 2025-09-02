<?php

use App\Http\Controllers\WhatsappController;
use Illuminate\Support\Facades\Route;

Route::name('whatsapp.')->prefix('whatsapp')->group(function () {
    Route::get('/messaging', [WhatsappController::class, 'index'])
        ->name('index');

    Route::post('/contacts', [WhatsappController::class, 'storeContact'])
        ->name('contacts.store');

    // Add this to your whatsapp routes section
    Route::put('/contacts/{id}', [WhatsappController::class, 'updateContact'])
        ->name('contacts.update');

    // Add this to your whatsapp routes section
    Route::delete('/contacts/{id}', [WhatsappController::class, 'destroyContact'])
        ->name('contacts.destroy');

    // Send message endpoint
    Route::post('/send', [WhatsappController::class, 'sendMessage'])
        ->name('send');

    Route::get('/messages/{phone}', [WhatsappController::class, 'getMessages'])
        ->name('whatsapp.messages.get');
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
