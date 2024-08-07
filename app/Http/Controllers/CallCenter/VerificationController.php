<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Models\CustomerQueue;
use Inertia\Inertia;

class VerificationController extends Controller
{
    public function create(CustomerQueue $customerQueue)
    {
        return Inertia::render('CallCenter/Verification/VerificationForm', [
            'customerQueue' => $customerQueue,
        ]);
    }
}
