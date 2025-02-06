<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Interfaces\CallCenter\VerificationRepositoryInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use App\Models\Token;
use App\Models\Verification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerificationController extends Controller
{
    public function __construct(
        private readonly QueueRepositoryInterface $queueRepository,
        private readonly VerificationRepositoryInterface $verificationRepository,
    ) {}

    public function getVerificationQueueList()
    {
        return Inertia::render('CallCenter/Verification/QueueList', [
            'verificationQueue' => $this->queueRepository->getDocumentVerificationQueue()->getData(),
            'verificationQueueCounts' => $this->queueRepository->getDocumentVerificationQueueCounts()->getData(),
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
            'hblId' => HBL::withoutGlobalScopes()->where('reference', $customerQueue->token->reference)->first()->id,
        ]);
    }

    public function store(Request $request)
    {
        $this->verificationRepository->storeVerification($request->all());
    }

    public function showVerifiedList()
    {
        return Inertia::render('CallCenter/Verification/VerifiedList');
    }

    public function getVerifiedList(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');

        return $this->verificationRepository->dataset($limit, $page, $order, $dir);
    }

    public function getHblPricing(Token $token)
    {
        return $this->verificationRepository->getHBLPaymentsDetails($token);
    }
}
