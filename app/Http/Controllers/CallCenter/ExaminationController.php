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
    ) {}

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
            'hblId' => HBL::withoutGlobalScopes()->where('reference', $customerQueue->token->reference)->first()->id,
        ]);
    }

    public function store(Request $request)
    {
        $this->examinationRepository->releaseHBL($request->all());
    }

    public function showGatePassList()
    {
        return Inertia::render('CallCenter/Examination/GatePassList', []);
    }

    public function getGatePassList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');

        return $this->examinationRepository->dataset($limit, $page, $order, $dir);
    }

    public function showReturnToBond(Request $request)
    {
        $tokenNumber = $request->input('token');
        
        if (!$tokenNumber) {
            return Inertia::render('CallCenter/Examination/ReturnToBond', [
                'packages' => [],
                'token' => null,
            ]);
        }

        $token = \App\Models\Token::where('token', $tokenNumber)->first();
        
        if (!$token) {
            return Inertia::render('CallCenter/Examination/ReturnToBond', [
                'packages' => [],
                'token' => null,
                'error' => 'Token not found',
            ]);
        }

        $hbl = HBL::withoutGlobalScopes()->where('reference', $token->reference)->first();
        
        if (!$hbl) {
            return Inertia::render('CallCenter/Examination/ReturnToBond', [
                'packages' => [],
                'token' => $token,
                'error' => 'HBL not found',
            ]);
        }

        // Get only held packages
        $heldPackages = $hbl->packages()->where('release_status', 'held')->get();

        return Inertia::render('CallCenter/Examination/ReturnToBond', [
            'packages' => $heldPackages,
            'token' => $token,
            'hbl' => $hbl,
        ]);
    }

    public function returnToBond(Request $request)
    {
        $this->examinationRepository->returnPackagesToBond($request->all());
        
        return redirect()->route('call-center.examination.return-to-bond')
            ->with('success', 'Packages returned to bond storage successfully');
    }

    public function completeToken(Request $request)
    {
        $this->examinationRepository->completeToken($request->all());
        
        return redirect()->back()->with('success', 'Token completed successfully');
    }
}
