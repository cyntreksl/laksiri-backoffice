<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Http\Requests\StorePackagePriceRequest;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\PackagePriceRepositoryInterface;
use App\Models\PackagePrice;
use Inertia\Inertia;

class PackagePriceController extends Controller
{
    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository,
        private readonly PackagePriceRepositoryInterface $packagePriceRepository,
    ) {
    }

    public function index()
    {
        return Inertia::render('Setting/PackagePricing/PriceList', [
            'packageRules' => $this->packagePriceRepository->getPackagePriceRules(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Setting/PackagePricing/CreatePackagePriceRule', [
            'cargoModes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'branches' => $this->branchRepository->getDestinationBranches(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePackagePriceRequest $request)
    {
        $this->packagePriceRepository->createPackagePriceRule($request->all());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PackagePrice $packagePrice)
    {
        return Inertia::render('Setting/PackagePricing/EditPackagePriceRule', [
            'cargoModes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'branches' => $this->branchRepository->getBranches(),
            'packageRule' => $packagePrice,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PackagePrice $packagePrice, StorePackagePriceRequest $request)
    {
        $this->packagePriceRepository->updatePackagePriceRule($packagePrice, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PackagePrice $packagePrice)
    {
        $this->packagePriceRepository->deletePackagePriceRule($packagePrice);
    }
}
