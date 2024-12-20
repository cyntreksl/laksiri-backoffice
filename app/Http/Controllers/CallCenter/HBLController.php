<?php

namespace App\Http\Controllers\CallCenter;

use App\Actions\HBL\GetHBLByIdWithPackages;
use App\Enum\HBLPaymentStatus;
use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\HBLRepositoryInterface;
use App\Interfaces\PriceRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HBLController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly HBLRepositoryInterface $HBLRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PriceRepositoryInterface $priceRepository,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('hbls.index');

        return Inertia::render('CallCenter/HBL/HBLList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'hbls' => $this->HBLRepository->getHBLsWithPackages(),
            'paymentStatus' => HBLPaymentStatus::cases(),
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
}
