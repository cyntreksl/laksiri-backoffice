<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Models\CustomerQueue;
use Inertia\Inertia;

class VerificationController extends Controller
{
    public function __construct(
        private readonly QueueRepositoryInterface $queueRepository,
    ) {
    }

    public function getVerificationQueueList()
    {
        return Inertia::render('CallCenter/Verification/QueueList', [
            'verificationQueue' => $this->queueRepository->getDocumentVerificationQueue()->getData(),
        ]);
    }

    public function create(CustomerQueue $customerQueue)
    {
        return Inertia::render('CallCenter/Verification/VerificationForm', [
            'customerQueue' => $customerQueue,
        ]);
    }
}
