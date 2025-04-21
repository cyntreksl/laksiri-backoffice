<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\PackageTypeRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpecialDOChargeController extends Controller
{
    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository,
        private readonly PackageTypeRepositoryInterface $packageTypeRepository,

    ) {}
    public function index()
    {
        return Inertia::render('Setting/SpecialDOCharge/SpecialDOChargesList',[
            'hblTypes' => HBLType::cases(),
            'branches' => $this->branchRepository->getDepartureBranches()->toArray(),
            'packageTypes' => $this->packageTypeRepository->getPackageTypes()->toArray(),
        ]);
    }

    public function create()
    {
//        dd($this->branchRepository->getDepartureBranches()->toArray(), $this->packageTypeRepository->getPackageTypes());
        return Inertia::render('Setting/SpecialDOCharge/CreateSpecialDOCharge',[
        'cargoModes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'branches' => $this->branchRepository->getDepartureBranches()->toArray(),
            'packageTypes' => $this->packageTypeRepository->getPackageTypes(),
        ]);
    }

}
