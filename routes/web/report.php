<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::name('report.')->group(function () {
    //Payment Summery
    Route::get('payment-summaries', function () {
        return Inertia::render('Report/PaymentSummeryList');
    })->name('payment-summaries.index');
});
