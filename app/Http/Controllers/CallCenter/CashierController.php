<?php

namespace App\Http\Controllers\CallCenter;

use App\Actions\Branch\GetBranchById;
use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\CashierRepositoryInterface;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashierController extends Controller
{
    public function __construct(
        private readonly QueueRepositoryInterface $queueRepository,
        private readonly CashierRepositoryInterface $cashierRepository
    ) {}

    public function getCashierQueueList()
    {

        return Inertia::render('CallCenter/Cashier/QueueList', [
            'cashierQueue' => $this->queueRepository->getCashierQueue()->getData(),
            'cashierQueueCounts' => $this->queueRepository->getCashierQueueCounts()->getData(),
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
                CustomerQueue::CASHIER_QUEUE,
                $customerQueue->token->customer_id,
                $customerQueue->token_id,
                now(),
                null,
            );
        }
        $hbl = HBL::withoutGlobalScopes()->where('reference', $customerQueue->token->reference)->first();

        $branch = GetBranchById::run($hbl->branch_id);

        return Inertia::render('CallCenter/Cashier/CashierForm', [
            'customerQueue' => $customerQueue,
            'hblId' => $hbl->id,
            'hbl' => $hbl,
            'doCharge' => $branch->do_charge,
        ]);
    }

    public function store(Request $request)
    {
        $this->cashierRepository->updatePayment($request->all());
    }

    public function showPaidList()
    {
        return Inertia::render('CallCenter/Cashier/PaidList');
    }

    public function getPaidList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');

        return $this->cashierRepository->dataset($limit, $page, $order, $dir);
    }
}
