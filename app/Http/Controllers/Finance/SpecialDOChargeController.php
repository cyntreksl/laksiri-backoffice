<?php

namespace App\Http\Controllers\Finance;

use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpecialDoChargeRequest;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\Finance\SpecialDOChargeRepositoryInterface;
use App\Interfaces\PackageTypeRepositoryInterface;
use App\Models\SpecialDOCharge;
use Inertia\Inertia;

class SpecialDOChargeController extends Controller
{
    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository,
        private readonly PackageTypeRepositoryInterface $packageTypeRepository,
        private readonly SpecialDOChargeRepositoryInterface $specialDOChargeRepository,
    ) {}

    public function index()
    {
        return Inertia::render('Setting/SpecialDOCharge/SpecialDOChargesList', [
            'charges' => $this->specialDOChargeRepository->getDOCharges(),
            'branches' => $this->branchRepository->getDepartureBranches()->toArray(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Setting/SpecialDOCharge/CreateSpecialDOCharge', [
            'cargoModes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'branches' => $this->branchRepository->getDepartureBranches()->toArray(),
            'packageTypes' => $this->packageTypeRepository->getPackageTypes(),
        ]);
    }

    public function store(StoreSpecialDoChargeRequest $request)
    {
        $this->specialDOChargeRepository->storeSpecialDOCharge($request->all());
    }

    public function destroy($id)
    {
        $specialDOCharge = SpecialDOCharge::findOrFail($id);

        $this->specialDOChargeRepository->deleteSpecialDOCharge($specialDOCharge);
    }
}
