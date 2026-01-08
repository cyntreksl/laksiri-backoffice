<?php

namespace App\Http\Controllers\CallCenter;

use App\Actions\Branch\GetDestinationBranches;
use App\Actions\HBL\GetHBLByIdWithPackages;
use App\Enum\ContainerStatus;
use App\Enum\HBLPaymentStatus;
use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\HBLRepositoryInterface;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\PriceRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\CallFlag;
use App\Models\Container;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HBLController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly HBLRepositoryInterface $HBLRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PriceRepositoryInterface $priceRepository,
        private readonly DriverRepositoryInterface $driverRepository,
    ) {}

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

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (! auth()->user()->canAny(['hbls.index', 'customer-queue.issue token'])) {
            abort(403, 'Unauthorized');
        }

        // Get shipments (containers) with specific statuses
        $shipments = Container::whereIn('status', [
            ContainerStatus::IN_TRANSIT->value,
            ContainerStatus::REACHED_DESTINATION->value,
            ContainerStatus::UNLOADED->value,
            ContainerStatus::LOADED->value,
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

        return Inertia::render('CallCenter/HBL/HBLList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
            'shipments' => $shipments,
        ]);
    }

    public function receptionIndex()
    {
        // Get shipments (containers) with specific statuses
        $shipments = Container::whereIn('status', [
            ContainerStatus::IN_TRANSIT->value,
            ContainerStatus::REACHED_DESTINATION->value,
            ContainerStatus::UNLOADED->value,
            ContainerStatus::LOADED->value,
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

        return Inertia::render('CallCenter/HBL/CallCenterHBLList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
            'shipments' => $shipments,
        ]);
    }

    /**
     * Display appointment list
     */
    public function appointmentList()
    {
        $this->authorize('hbls.index');

        return Inertia::render('CallCenter/HBL/AppointmentList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
        ]);
    }

    /**
     * Display follow-up list
     */
    public function followupList()
    {
        $this->authorize('hbls.index');

        return Inertia::render('CallCenter/HBL/FollowupList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
        ]);
    }

    /**
     * Display all calls list
     */
    public function allCallsList()
    {
        $this->authorize('hbls.index');

        return Inertia::render('CallCenter/HBL/AllCallsList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
        ]);
    }

    /**
     * Get all calls data for API
     */
    public function getAllCallsData(Request $request)
    {
        $this->authorize('hbls.index');

        $query = CallFlag::with(['hbl', 'causer'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'LIKE', "%{$search}%")
                    ->orWhere('caller', 'LIKE', "%{$search}%")
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

        if ($request->filled('call_outcome')) {
            $query->where('call_outcome', $request->call_outcome);
        }

        if ($request->filled('agent')) {
            $query->where('created_by', $request->agent);
        }

        if ($request->filled('fromDate') && $request->filled('toDate')) {
            $query->whereBetween('created_at', [
                $request->fromDate.' 00:00:00',
                $request->toDate.' 23:59:59',
            ]);
        }

        // Apply HBL-related filters through relationship
        if ($request->filled('warehouse') || $request->filled('deliveryType') || $request->filled('cargoMode') || $request->filled('paymentStatus') || $request->filled('createdBy')) {
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
            });
        }

        // Sorting
        if ($request->filled('sort_field') && $request->filled('sort_order')) {
            $sortField = $request->sort_field;
            $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

            if ($sortField === 'created_at') {
                $query->orderBy('created_at', $sortOrder);
            } elseif ($sortField === 'call_outcome') {
                $query->orderBy('call_outcome', $sortOrder);
            } elseif ($sortField === 'followup_date') {
                $query->orderBy('followup_date', $sortOrder);
            } elseif ($sortField === 'appointment_date') {
                $query->orderBy('appointment_date', $sortOrder);
            }
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $callFlags = $query->paginate($perPage);

        return response()->json([
            'data' => $callFlags->items(),
            'meta' => [
                'current_page' => $callFlags->currentPage(),
                'last_page' => $callFlags->lastPage(),
                'per_page' => $callFlags->perPage(),
                'total' => $callFlags->total(),
                'from' => $callFlags->firstItem(),
                'to' => $callFlags->lastItem(),
            ],
        ]);
    }

    /**
     * Get appointments data for API
     */
    public function getAppointmentsData(Request $request)
    {
        $this->authorize('hbls.index');

        $query = CallFlag::with(['hbl', 'causer'])
            ->whereNotNull('appointment_date')
            ->orderBy('appointment_date', 'asc');

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
        if ($request->filled('warehouse') || $request->filled('deliveryType') || $request->filled('cargoMode') || $request->filled('paymentStatus') || $request->filled('createdBy')) {
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
            });
        }

        // Sorting
        if ($request->filled('sort_field') && $request->filled('sort_order')) {
            $sortField = $request->sort_field;
            $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

            if ($sortField === 'appointment_date') {
                $query->orderBy('appointment_date', $sortOrder);
            } elseif ($sortField === 'created_at') {
                $query->orderBy('created_at', $sortOrder);
            }
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $callFlags = $query->paginate($perPage);

        return response()->json([
            'data' => $callFlags->items(),
            'meta' => [
                'current_page' => $callFlags->currentPage(),
                'last_page' => $callFlags->lastPage(),
                'per_page' => $callFlags->perPage(),
                'total' => $callFlags->total(),
                'from' => $callFlags->firstItem(),
                'to' => $callFlags->lastItem(),
            ],
        ]);
    }

    /**
     * Get followups data for API
     */
    public function getFollowupsData(Request $request)
    {
        $this->authorize('hbls.index');

        $query = CallFlag::with(['hbl', 'causer'])
            ->whereNotNull('followup_date')
            ->orderBy('followup_date', 'asc');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'LIKE', "%{$search}%")
                    ->orWhere('caller', 'LIKE', "%{$search}%")
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
            $query->whereBetween('followup_date', [
                $request->fromDate,
                $request->toDate,
            ]);
        }

        // Apply HBL-related filters through relationship
        if ($request->filled('warehouse') || $request->filled('deliveryType') || $request->filled('cargoMode') || $request->filled('paymentStatus') || $request->filled('createdBy')) {
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
            });
        }

        // Sorting
        if ($request->filled('sort_field') && $request->filled('sort_order')) {
            $sortField = $request->sort_field;
            $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';

            if ($sortField === 'followup_date') {
                $query->orderBy('followup_date', $sortOrder);
            } elseif ($sortField === 'created_at') {
                $query->orderBy('created_at', $sortOrder);
            }
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $callFlags = $query->paginate($perPage);

        return response()->json([
            'data' => $callFlags->items(),
            'meta' => [
                'current_page' => $callFlags->currentPage(),
                'last_page' => $callFlags->lastPage(),
                'per_page' => $callFlags->perPage(),
                'total' => $callFlags->total(),
                'from' => $callFlags->firstItem(),
                'to' => $callFlags->lastItem(),
            ],
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);
        $shipment = $request->input('shipment', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'createdBy', 'deliveryType', 'warehouse', 'isHold', 'paymentStatus', 'isDelayed', 'drivers', 'officers']);
        if ($shipment) {
            $filters['shipment'] = $shipment;
        }

        return $this->HBLRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function createToken($hbl)
    {
        $hbl = GetHBLByIdWithPackages::run($hbl);

        return $this->HBLRepository->createAndIssueToken($hbl);
    }

    public function createTokenWithVerification(Request $request, $hbl)
    {
        $request->validate([
            'is_checked' => 'nullable|array',
            'note' => 'nullable|string|max:1000',
        ]);

        $hbl = GetHBLByIdWithPackages::run($hbl);

        $result = $this->HBLRepository->createAndIssueTokenWithVerification($hbl, $request->only(['is_checked', 'note']));

        // If it's an Inertia request, return the token data without redirect
        if ($request->header('X-Inertia')) {
            $resultData = $result->getData();

            return response()->json([
                'success' => true,
                'message' => 'Token issued successfully!',
                'token' => $resultData->token ?? null,
                'download_url' => route('call-center.hbls.download-token', $resultData->token->id ?? ''),
                'print_url' => route('call-center.hbls.print-token', $resultData->token->id ?? ''),
                'hbl' => [
                    'hbl_number' => $hbl->hbl_number,
                    'hbl_name' => $hbl->hbl_name,
                    'reference' => $hbl->reference,
                    'consignee_name' => $hbl->consignee_name,
                ],
            ]);
        }

        // For non-Inertia requests, return original JSON
        return $result;
    }

    public function showDoorToDoorList()
    {
        $this->authorize('hbls.index');

        return Inertia::render('CallCenter/HBL/DoorToDoorList', [
            'drivers' => $this->driverRepository->getAllDrivers(),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'users' => $this->userRepository->getUsers(['customer']),
        ]);
    }

    public function getDoorToDoorList(Request $request): JsonResponse
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'isHold', 'drivers', 'officers', 'paymentStatus', 'warehouse']);

        return $this->HBLRepository->getDoorToDoorHBL($limit, $page, $order, $dir, $search, $filters);
    }

    public function downloadToken($tokenId)
    {
        return $this->HBLRepository->generateTokenPDF($tokenId, 'download');
    }

    public function printToken($tokenId)
    {
        return $this->HBLRepository->generateTokenPDF($tokenId, 'print');
    }

    /**
     * Display baggage receipt generation page
     */
    public function baggageReceiptIndex()
    {
        $this->authorize('hbls.baggage-receipt');

        return Inertia::render('CallCenter/HBL/BaggageReceiptGenerate');
    }

    /**
     * Get shipments (containers) that have arrived at warehouse
     */
    public function getBaggageReceiptShipments(Request $request)
    {
        $this->authorize('hbls.baggage-receipt');

        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'arrived_at_primary_warehouse');
        $dir = $request->input('sort_order', 'desc');
        $search = $request->input('search', null);
        $fromDate = $request->input('fromDate', null);
        $toDate = $request->input('toDate', null);

        $query = Container::where('status', ContainerStatus::ARRIVED_PRIMARY_WAREHOUSE->value)
            ->orWhereNotNull('arrived_at_primary_warehouse');

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', '%' . $search . '%')
                    ->orWhere('bl_number', 'like', '%' . $search . '%')
                    ->orWhere('vessel_name', 'like', '%' . $search . '%')
                    ->orWhere('port_of_discharge', 'like', '%' . $search . '%');
            });
        }

        // Apply date range filter
        if ($fromDate && $toDate) {
            $query->whereBetween('arrived_at_primary_warehouse', [$fromDate, $toDate]);
        }

        // Get paginated results with HBL count
        $shipments = $query->withCount('hbl_packages as hbl_count')
            ->orderBy($order, $dir)
            ->paginate($limit, [
                'id',
                'reference',
                'bl_number',
                'awb_number',
                'container_number',
                'container_type',
                'cargo_type',
                'vessel_name',
                'port_of_discharge',
                'arrived_at_primary_warehouse',
                'estimated_time_of_arrival',
                'estimated_time_of_departure',
            ], 'page', $page);

        return response()->json([
            'data' => $shipments->items(),
            'meta' => [
                'total' => $shipments->total(),
                'current_page' => $shipments->currentPage(),
                'per_page' => $shipments->perPage(),
                'last_page' => $shipments->lastPage(),
                'from' => $shipments->firstItem(),
                'to' => $shipments->lastItem(),
            ],
        ]);
    }

    /**
     * Generate all baggage receipts for a container as single PDF
     */
    public function generateAllBaggageReceipts(Container $container)
    {
        $this->authorize('hbls.baggage-receipt');

        return $this->HBLRepository->generateAllBaggageReceipts($container);
    }

    /**
     * Stream all baggage receipts for printing
     */
    public function streamAllBaggageReceipts(Container $container)
    {
        $this->authorize('hbls.baggage-receipt');

        return $this->HBLRepository->streamAllBaggageReceipts($container);
    }

    /**
     * Generate ZIP file with individual PDFs for each HBL
     */
    public function generateBaggageReceiptsZip(Container $container)
    {
        $this->authorize('hbls.baggage-receipt');

        return $this->HBLRepository->generateBaggageReceiptsZip($container);
    }
}

