<?php

namespace App\Http\Controllers\Finance;

use App\Actions\Branch\GetDestinationBranches;
use App\Enum\ContainerStatus;
use App\Enum\HBLPaymentStatus;
use App\Http\Controllers\Controller;
use App\Interfaces\Finance\HBLRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Container;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HBLController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly HBLRepositoryInterface $HBLRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Get shipment options for containers with specific statuses.
     */
    private function getShipmentOptions()
    {
        return Container::whereIn('status', [
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
    }

    /**
     * Display a listing of the resource.
     */
    public function approveHBLs()
    {
        $this->authorize('hbls.hbl finance approval list');

        // Get shipments (containers) with specific statuses
        $shipments = $this->getShipmentOptions();

        return Inertia::render('Finance/HBL/FinanceHBLList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
            'shipments' => $shipments,
        ]);
    }

    public function approvedHBLs()
    {
        $this->authorize('hbls.finance approved hbl list');

        // Get shipments (containers) with specific statuses
        $shipments = $this->getShipmentOptions();

        return Inertia::render('Finance/HBL/FinanceApprovedHBLList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
            'shipments' => $shipments,
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

        $filters = $request->only(['userData', 'fromDate', 'toDate', 'cargoMode', 'createdBy', 'deliveryType', 'warehouse', 'isHold', 'paymentStatus']);
        if ($shipment) {
            $filters['shipment'] = $shipment;
        }

        return $this->HBLRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function approvedHBLList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);
        $shipment = $request->input('shipment', null);

        $filters = $request->only(['userData', 'fromDate', 'toDate', 'cargoMode', 'createdBy', 'deliveryType', 'warehouse', 'isHold', 'paymentStatus']);
        if ($shipment) {
            $filters['shipment'] = $shipment;
        }

        return $this->HBLRepository->approvedDataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function makeFinanceApproval(Request $request)
    {
        $this->authorize('hbls.create finance approval');

        $hblIds = $request->hbl_ids;

        return $this->HBLRepository->financeApproved($hblIds);
    }

    public function removeFinanceApproval(Request $request)
    {
        $this->authorize('hbls.create finance approval');

        $hblIds = $request->hbl_ids;

        return $this->HBLRepository->removeFinanceApproval($hblIds);
    }
}
