<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\ContainerStatus;
use App\Enum\ContainerType;
use App\Interfaces\CallCenter\ExaminationRepositoryInterface;
use App\Interfaces\ContainerRepositoryInterface;
use App\Models\Container;
use App\Models\CustomerQueue;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GateControlController extends Controller
{
    public function __construct(
        private readonly ContainerRepositoryInterface $containerRepository,
        private readonly ExaminationRepositoryInterface $examinationRepository,
    ) {}

    public function listInboundShipments()
    {
        $seaContainerOptions = ContainerType::getSeaCargoOptions();
        $airContainerOptions = ContainerType::getAirCargoOptions();

        $containerStatuses = array_values(array_filter(ContainerStatus::cases(), function ($status) {
            return ! in_array($status->name, ['DRAFT', 'REQUESTED']);
        }));

        return Inertia::render('GateControl/InboundShipments', [
            'cargoTypes' => CargoType::cases(),
            'containerTypes' => ContainerType::cases(),
            'containers' => $this->containerRepository->getLoadedContainers(),
            'containerStatus' => $containerStatuses,
            'seaContainerOptions' => $seaContainerOptions,
            'airContainerOptions' => $airContainerOptions,
        ]);
    }

    public function getAfterDispatchShipmentsList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'etdStartDate', 'etdEndDate', 'cargoType', 'containerType', 'status', 'branch']);

        return $this->containerRepository->getAfterDispatchShipmentsList($limit, $page, $order, $dir, $search, $filters);
    }

    public function updateInboundShipmentStatus(Container $container)
    {
        $this->containerRepository->updateInboundShipmentStatus($container);
    }

    public function listOutboundShipments()
    {
        $seaContainerOptions = ContainerType::getSeaCargoOptions();
        $airContainerOptions = ContainerType::getAirCargoOptions();

        $containerStatuses = array_values(array_filter(ContainerStatus::cases(), function ($status) {
            return ! in_array($status->name, ['DRAFT', 'REQUESTED']);
        }));

        return Inertia::render('GateControl/OutboundShipments', [
            'cargoTypes' => CargoType::cases(),
            'containerTypes' => ContainerType::cases(),
            'containers' => $this->containerRepository->getLoadedContainers(),
            'containerStatus' => $containerStatuses,
            'seaContainerOptions' => $seaContainerOptions,
            'airContainerOptions' => $airContainerOptions,
        ]);
    }

    public function getAfterInboundShipmentsList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'etdStartDate', 'etdEndDate', 'cargoType', 'containerType', 'status', 'branch']);

        return $this->containerRepository->getAfterInboundShipmentsList($limit, $page, $order, $dir, $search, $filters);
    }

    public function updateOutboundShipmentStatus(Container $container)
    {
        $this->containerRepository->updateOutboundShipmentStatus($container);
    }

    public function listOutboundGatePasses()
    {
        return Inertia::render('GateControl/OutboundTokens');
    }

    public function markAsDeparted(CustomerQueue $customerQueue)
    {
        try {
            $this->examinationRepository->markAsDeparted($customerQueue);
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as depart: '.$e->getMessage());
        }
    }

    public function showCompleteToken(Request $request)
    {
        $tokenNumber = $request->input('token');
        
        if (!$tokenNumber) {
            return Inertia::render('GateControl/CompleteToken', [
                'token' => null,
                'packageSummary' => null,
            ]);
        }

        $token = \App\Models\Token::where('token', $tokenNumber)->first();
        
        if (!$token) {
            return Inertia::render('GateControl/CompleteToken', [
                'token' => null,
                'packageSummary' => null,
                'error' => 'Token not found',
            ]);
        }

        $hbl = \App\Models\HBL::withoutGlobalScopes()->where('reference', $token->reference)->first();
        
        if (!$hbl) {
            return Inertia::render('GateControl/CompleteToken', [
                'token' => $token,
                'packageSummary' => null,
                'error' => 'HBL not found',
            ]);
        }

        $packageQueue = \App\Models\PackageQueue::where('token_id', $token->id)->first();

        // Get package summary
        $packages = $hbl->packages;
        $summary = [
            'total' => $packages->count(),
            'released' => $packages->where('release_status', 'released')->count(),
            'held' => $packages->where('release_status', 'held')->count(),
            'returned_to_bond' => $packages->where('release_status', 'returned_to_bond')->count(),
            'pending' => $packages->where('release_status', 'pending')->count(),
        ];

        return Inertia::render('GateControl/CompleteToken', [
            'token' => $token,
            'hbl' => $hbl,
            'packageQueue' => $packageQueue,
            'packageSummary' => $summary,
            'packages' => $packages,
        ]);
    }

    public function completeToken(Request $request)
    {
        try {
            $this->examinationRepository->completeToken($request->all());
            return redirect()->back()->with('success', 'Token completed successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function listCompletedTokens()
    {
        return Inertia::render('GateControl/CompletedTokens');
    }

    public function getCompletedTokensList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'completed_at');
        $dir = $request->input('sort_order', 'desc');
        $search = $request->input('search', null);

        $query = \App\Models\Token::query()
            ->with(['customer', 'hbl.packages'])
            ->where('status', 'completed')
            ->whereNotNull('departed_at');

        // Search functionality
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('token', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $tokens = $query->orderBy($order, $dir)->paginate($limit, ['*'], 'page', $page);

        // Transform data to include package summary
        $data = $tokens->map(function ($token) {
            $hbl = $token->hbl;
            $packageQueue = \App\Models\PackageQueue::where('token_id', $token->id)->first();
            
            $packageSummary = null;
            if ($hbl) {
                $packages = $hbl->packages;
                $packageSummary = [
                    'total' => $packages->count(),
                    'released' => $packages->where('release_status', 'released')->count(),
                    'held' => $packages->where('release_status', 'held')->count(),
                    'returned_to_bond' => $packages->where('release_status', 'returned_to_bond')->count(),
                ];
            }

            return [
                'id' => $token->id,
                'token' => $token->token,
                'reference' => $token->reference,
                'customer' => $token->customer->name ?? 'Unknown',
                'customer_id' => $token->customer_id,
                'status' => $token->status,
                'departed_at' => $token->departed_at,
                'completed_at' => $packageQueue->completed_at ?? $token->departed_at,
                'package_summary' => $packageSummary,
                'hbl_id' => $hbl->id ?? null,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $tokens->total(),
                'current_page' => $tokens->currentPage(),
                'per_page' => $tokens->perPage(),
                'last_page' => $tokens->lastPage(),
            ],
        ]);
    }
}
