<?php

namespace App\Http\Controllers\CallCenter;

use App\Actions\Branch\GetBranchById;
use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\CashierRepositoryInterface;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Models\Currency;
use App\Models\CustomerQueue;
use App\Models\HBL;
use App\Models\User;
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
        $currencyRate = Currency::where('currency_symbol', $branch->currency_symbol)->first()?->sl_rate ?? 1;

        return Inertia::render('CallCenter/Cashier/CashierForm', [
            'customerQueue' => $customerQueue,
            'hblId' => $hbl->id,
            'hbl' => $hbl,
            'doCharge' => $branch->do_charge,
            'branch' => $branch,
            'currencyRate' => $currencyRate,
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
        $search = $request->input('search', null);

        $filters = [
            'customer' => $request->input('customer'),
            'reception' => $request->input('reception'),
            'verified_by' => $request->input('verified_by'),
            'paid_at' => $request->input('paid_at'),
        ];

        return $this->cashierRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function searchCustomers(Request $request)
    {
        $search = $request->input('search', '');
        
        $customers = User::role('customer')
            ->select('id', 'name')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get();
        
        return response()->json($customers);
    }

    public function searchUsers(Request $request)
    {
        $search = $request->input('search', '');
        
        $users = User::role('call center')
            ->select('id', 'name')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get();
        
        return response()->json($users);
    }
}
