<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\ExaminationRepositoryInterface;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExaminationController extends Controller
{
    public function __construct(
        private readonly QueueRepositoryInterface $queueRepository,
        private readonly ExaminationRepositoryInterface $examinationRepository,
    ) {
    }

    public function getExaminationQueueList()
    {
        return Inertia::render('CallCenter/Examination/QueueList', [
            'examinationQueue' => $this->queueRepository->getExaminationQueue()->getData(),
            'examinationQueueCounts' => $this->queueRepository->getExaminationQueueCounts()->getData(),
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
                CustomerQueue::EXAMINATION_QUEUE,
                $customerQueue->token->customer_id,
                $customerQueue->token_id,
                now(),
                null,
            );
        }

        return Inertia::render('CallCenter/Examination/ExaminationForm', [
            'customerQueue' => $customerQueue,
            'reference' => $customerQueue->token->reference,
            'hblId' => HBL::where('reference', $customerQueue->token->reference)->first()->id,
        ]);
    }

    public function store(Request $request)
    {
        $this->examinationRepository->releaseHBL($request->all());
    }
}
