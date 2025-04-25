<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Http\Requests\StoreSpecialDoChargeRequest;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\PackageTypeRepositoryInterface;
use App\Interfaces\SpecialDOChargeRepositoryInterface;
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
            'hblTypes' => HBLType::cases(),
            'branches' => $this->branchRepository->getDepartureBranches()->toArray(),
            'packageTypes' => $this->packageTypeRepository->getPackageTypes()->toArray(),
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
}
