<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Interfaces\CallCenter\VerificationRepositoryInterface;
use App\Models\CustomerQueue;
use App\Models\Verification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerificationController extends Controller
{
    public function __construct(
        private readonly QueueRepositoryInterface $queueRepository,
        private readonly VerificationRepositoryInterface $verificationRepository,
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
        if (! $customerQueue->arrived_at) {
            $customerQueue->update([
                'arrived_at' => now(),
            ]);

            // set queue status log
            $customerQueue->addQueueStatus(
                CustomerQueue::DOCUMENT_VERIFICATION_QUEUE,
                $customerQueue->token->customer_id,
                $customerQueue->token_id,
                now(),
                null,
            );
        }

        return Inertia::render('CallCenter/Verification/VerificationForm', [
            'customerQueue' => $customerQueue,
            'verificationDocuments' => Verification::verification_documents(),
        ]);
    }

    public function store(Request $request)
    {
        $this->verificationRepository->storeVerification($request->all());
    }
}
