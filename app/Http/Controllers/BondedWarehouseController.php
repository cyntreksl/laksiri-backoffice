<?php

namespace App\Http\Controllers;

use App\Enum\HBLType;
use App\Interfaces\BondedWarehouseRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BondedWarehouseController extends Controller
{
    public function __construct(
        private readonly BondedWarehouseRepositoryInterface $bondedWarehouseRepository,
    ) {
    }

    public function index()
    {
        return Inertia::render('Arrival/BondedWarehouseList', [
            'hblTypes' => HBLType::cases(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'deliveryType']);

        return $this->bondedWarehouseRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function markAsShortLoading($hbl_id)
    {
        $this->bondedWarehouseRepository->markAsShortLoading($hbl_id);
    }
}
