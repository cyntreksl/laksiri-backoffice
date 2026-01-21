<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Interfaces\CallCenter\ReceptionRepositoryInterface;
use App\Models\CallFlag;
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
    ) {}

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

        return Inertia::render('CallCenter/Reception/Verification', [
            'customerQueue' => $customerQueue,
            'verificationDocuments' => $documents,
            'hblId' => HBL::withoutGlobalScopes()->where('reference', $customerQueue->token->reference)->first()->id,
        ]);
    }

    public function store(Request $request)
    {
        $this->receptionRepository->storeVerification($request->all());
    }

    public function showReceptionVerifiedList()
    {
        return Inertia::render('CallCenter/Reception/ReceptionVerifiedList');
    }

    public function getReceptionVerifiedList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');

        return $this->receptionRepository->dataset($limit, $page, $order, $dir);
    }

    public function appointmentList()
    {
        // Get shipments (containers) with specific statuses
        $shipments = \App\Models\Container::whereIn('status', [
            \App\Enum\ContainerStatus::IN_TRANSIT->value,
            \App\Enum\ContainerStatus::REACHED_DESTINATION->value,
            \App\Enum\ContainerStatus::UNLOADED->value,
            \App\Enum\ContainerStatus::LOADED->value,
        ])
            ->select('id', 'reference', 'container_number', 'vessel_name', 'status', 'estimated_time_of_arrival')
            ->get()
            ->map(function ($container) {
                return [
                    'id' => $container->id,
                    'name' => ($container->container_number ?? $container->reference).' - '.$container->status.' ('.($container->vessel_name ?? 'Unknown Vessel').')',
                    'value' => $container->id,
                ];
            });

        return Inertia::render('CallCenter/Reception/AppointmentList', [
            'users' => app(\App\Interfaces\UserRepositoryInterface::class)->getUsers(['customer']),
            'paymentStatus' => \App\Enum\HBLPaymentStatus::cases(),
            'warehouses' => \App\Actions\Branch\GetDestinationBranches::run(),
            'shipments' => $shipments,
        ]);
    }

    public function getAppointmentsData(Request $request)
    {
        $query = CallFlag::with(['hbl.tokens.customerQueue', 'hbl.latestDetainRecord', 'causer'])
            ->whereNotNull('appointment_date');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'LIKE', "%{$search}%")
                    ->orWhere('caller', 'LIKE', "%{$search}%")
                    ->orWhere('appointment_notes', 'LIKE', "%{$search}%")
                    ->orWhereHas('hbl', function ($hblQuery) use ($search) {
                        $hblQuery->where('hbl_number', 'LIKE', "%{$search}%")
                            ->orWhere('hbl', 'LIKE', "%{$search}%")
                            ->orWhere('hbl_name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%")
                            ->orWhere('contact_number', 'LIKE', "%{$search}%")
                            ->orWhere('consignee_name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('causer', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($request->filled('agent')) {
            $query->where('created_by', $request->agent);
        }

        if ($request->filled('fromDate') && $request->filled('toDate')) {
            $query->whereBetween('appointment_date', [
                $request->fromDate,
                $request->toDate,
            ]);
        }

        // Apply HBL-related filters through relationship
        if ($request->filled('warehouse') || $request->filled('deliveryType') || $request->filled('cargoMode') || $request->filled('paymentStatus') || $request->filled('createdBy') || $request->filled('shipment')) {
            $query->whereHas('hbl', function ($hblQuery) use ($request) {
                if ($request->filled('warehouse')) {
                    $hblQuery->where('warehouse', $request->warehouse);
                }
                if ($request->filled('deliveryType')) {
                    $hblQuery->where('hbl_type', $request->deliveryType);
                }
                if ($request->filled('cargoMode')) {
                    $hblQuery->where('cargo_type', $request->cargoMode);
                }
                if ($request->filled('paymentStatus')) {
                    $this->applyPaymentStatusFilter($hblQuery, $request->paymentStatus);
                }
                if ($request->filled('createdBy')) {
                    $hblQuery->where('created_by', $request->createdBy);
                }
                if ($request->filled('shipment')) {
                    $hblQuery->where('container_id', $request->shipment);
                }
            });
        }

        // Sorting
        $sortField = $request->get('sort_field', 'appointment_date');
        $sortOrder = $request->get('sort_order', 'asc') === 'asc' ? 'asc' : 'desc';

        // Handle direct CallFlag fields
        if ($sortField === 'appointment_date' || $sortField === 'created_at') {
            $query->orderBy($sortField, $sortOrder);
        }
        // Handle nested HBL fields using join
        elseif (str_starts_with($sortField, 'hbl.')) {
            $hblField = str_replace('hbl.', '', $sortField);

            // Use leftJoin to sort by HBL fields
            $query->leftJoin('hbl', 'call_flags.hbl_id', '=', 'hbl.id')
                ->select('call_flags.*')
                ->orderBy('hbl.' . $hblField, $sortOrder);
        } else {
            // Default sorting
            $query->orderBy('appointment_date', 'asc');
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $appointments = $query->paginate($perPage);

        return response()->json([
            'data' => $appointments->items(),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
                'from' => $appointments->firstItem(),
                'to' => $appointments->lastItem(),
            ],
        ]);
    }

    /**
     * Apply payment status filter to HBL query
     */
    private function applyPaymentStatusFilter($hblQuery, $paymentStatus)
    {
        // Calculate payment status based on paid_amount and grand_total
        if ($paymentStatus === 'Full Paid') {
            $hblQuery->whereRaw('paid_amount >= grand_total');
        } elseif ($paymentStatus === 'Partial Paid') {
            $hblQuery->whereRaw('paid_amount > 0 AND paid_amount < grand_total');
        } elseif ($paymentStatus === 'Not Paid') {
            $hblQuery->whereRaw('(paid_amount = 0 OR paid_amount IS NULL)');
        }
    }
}
