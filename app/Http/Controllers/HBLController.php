<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Enum\WarehouseType;
use App\Http\Requests\StoreHBLRequest;
use App\Http\Requests\UpdateHBLRequest;
use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\HBL;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HBLController extends Controller
{
    public function __construct(
        private readonly HBLRepositoryInterface $HBLRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('HBL/HBLList', [
            'users' => $this->userRepository->getUsers(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'createdBy', 'hblType', 'warehouse']);

        return $this->HBLRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('HBL/CreateHBL', [
            'cargoTypes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'warehouses' => WarehouseType::cases(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHBLRequest $request)
    {
        $this->HBLRepository->storeHBL($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(HBL $hBL)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HBL $hBL)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHBLRequest $request, HBL $hBL)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HBL $hbl)
    {
        $this->HBLRepository->deleteHBL($hbl);
    }

    public function toggleHold(HBL $hbl)
    {
        $this->HBLRepository->toggleHold($hbl);
    }
}
