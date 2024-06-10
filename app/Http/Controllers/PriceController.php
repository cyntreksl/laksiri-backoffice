<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Http\Requests\StoreBranchPriceRequest;
use App\Http\Requests\UpdateBranchPriceRequest;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\PriceRepositoryInterface;
use App\Models\BranchPrice;
use Inertia\Inertia;

class PriceController extends Controller
{
    public function __construct(
        private readonly PriceRepositoryInterface $priceRepository,
        private readonly BranchRepositoryInterface $branchRepository,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Setting/Pricing/PriceList', [
            'priceRules' => $this->priceRepository->getPriceRules(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Setting/Pricing/CreatePriceRule', [
            'cargoModes' => CargoType::cases(),
            'branches' => $this->branchRepository->getDestinationBranches(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBranchPriceRequest $request)
    {
        $this->priceRepository->createPriceRule($request->all());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($branchPrice)
    {
        $branchPrice = BranchPrice::find($branchPrice);

        return Inertia::render('Setting/Pricing/EditPriceRule', [
            'cargoModes' => CargoType::cases(),
            'branches' => $this->branchRepository->getBranches(),
            'priceRule' => $branchPrice,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBranchPriceRequest $request, $branchPrice)
    {
        $branchPrice = BranchPrice::find($branchPrice);

        $this->priceRepository->updatePriceRule($request->all(), $branchPrice);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($branchPrice)
    {
        $branchPrice = BranchPrice::find($branchPrice);

        $this->priceRepository->deletePriceRule($branchPrice);
    }
}
