<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Interfaces\CallCenter\ReceptionRepositoryInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use App\Models\ReceptionVerification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReceptionController extends Controller
{
    public function __construct(
        private readonly QueueRepositoryInterface $queueRepository,
        private readonly ReceptionRepositoryInterface $receptionRepository,
    ) {
    }

    public function getReceptionQueueList()
    {
        return Inertia::render('CallCenter/Reception/QueueList', [
            'receptionQueue' => $this->queueRepository->getReceptionQueue()->getData(),
            'receptionQueueCounts' => $this->queueRepository->getReceptionQueueCounts()->getData(),
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
                CustomerQueue::RECEPTION_VERIFICATION_QUEUE,
                $customerQueue->token->customer_id,
                $customerQueue->token_id,
                now(),
                null,
            );
        }

        $hbl = (HBL::withoutGlobalScopes()->where('reference', $customerQueue->token->reference)->first());
        $documents = ReceptionVerification::reception_verification_documents();
        if ($hbl->hbl_type === 'UPB') {
            $documents = array_filter($documents, fn ($doc) => $doc !== 'NIC');
        } else {
            $documents = array_filter($documents, fn ($doc) => $doc !== 'Passport');
        }

        return Inertia::render('CallCenter/Reception/VerificationForm', [
            'customerQueue' => $customerQueue,
            'verificationDocuments' => $documents,
            'hblId' => HBL::withoutGlobalScopes()->where('reference', $customerQueue->token->reference)->first()->id,
        ]);
    }

    public function store(Request $request)
    {
        $this->receptionRepository->storeVerification($request->all());
    }

    public function getReceptionList(Request $request)
    {
        //        $limit = $request->input('limit', 10);
        //        $page = $request->input('offset', 1);
        //        $order = $request->input('order', 'id');
        //        $dir = $request->input('dir', 'asc');
        //
        //        return $this->verificationRepository->dataset($limit, $page, $order, $dir);
    }
}
