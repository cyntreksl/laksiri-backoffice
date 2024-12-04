<?php

namespace App\Http\Controllers\CallCenter;

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
    ) {
    }

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

        return Inertia::render('CallCenter/Cashier/CashierForm', [
            'customerQueue' => $customerQueue,
            'hblId' => HBL::withoutGlobalScopes()->where('reference', $customerQueue->token->reference)->first()->id,
        ]);
    }

    public function store(Request $request)
    {
        //        $this->cashierRepository->updatePayment($request->all());
        return $this->cashierRepository->downloadGatePass($request->all());
    }

    public function showPaidList()
    {
        return Inertia::render('CallCenter/Cashier/PaidList');
    }

    public function getPaidList(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');

        return $this->cashierRepository->dataset($limit, $page, $order, $dir);
    }

    public function getCashierReceipt($hbl)
    {
        return $this->cashierRepository->downloadGatePass($hbl);
    }
}
