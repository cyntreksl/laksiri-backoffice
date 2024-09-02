<?php

use App\Http\Controllers\UserFeedbackController;
use Illuminate\Support\Facades\Route;

Route::get('/email/{userId}/{hblId}/{token}', [UserFeedbackController::class, 'testSendEmail']);
Route::get('/your-feedback', [UserFeedbackController::class, 'viewFeedbackForm']);
Route::post('/feedback/upload', [UserFeedbackController::class, 'storeUserFeedback'])
    ->name('feedback.upload');
