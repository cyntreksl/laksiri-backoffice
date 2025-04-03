<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAirLineRequest;
use App\Http\Requests\UpdateAirLineRequest;
use App\Interfaces\AirLineRepositoryInterface;
use App\Models\AirLine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AirLineController extends Controller
{
    public function __construct(
        private readonly AirLineRepositoryInterface $airLineRepository,
    ) {}

    public function index()
    {
        return Inertia::render('Setting/AirLine/AirLineList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'drivers', 'officers', 'paymentStatus', 'deliveryType', 'warehouse']);

        return $this->airLineRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function store(StoreAirLineRequest $request)
    {
        $this->airLineRepository->createAirLine($request->all());
    }

    public function update(AirLine $airLine, UpdateAirLineRequest $request)
    {
        $this->airLineRepository->updateAirLine($airLine, $request->all());
    }

    public function destroy(AirLine $airLine)
    {
        $this->airLineRepository->destroyAirLine($airLine);
    }
}
