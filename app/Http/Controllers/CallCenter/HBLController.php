<?php

namespace App\Http\Controllers\CallCenter;

use App\Actions\Branch\GetDestinationBranches;
use App\Actions\HBL\GetHBLByIdWithPackages;
use App\Enum\HBLPaymentStatus;
use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\HBLRepositoryInterface;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\PriceRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
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
     * Display a listing of the resource.
     */
    public function index()
    {
        if (! auth()->user()->canAny(['hbls.index', 'customer-queue.issue token'])) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('CallCenter/HBL/HBLList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'hbls' => $this->HBLRepository->getHBLsWithPackages(),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'createdBy', 'hblType', 'warehouse', 'isHold', 'paymentStatus', 'isDelayed']);

        return $this->HBLRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function createToken($hbl)
    {
        $hbl = GetHBLByIdWithPackages::run($hbl);

        return $this->HBLRepository->createAndIssueToken($hbl);
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
}
