<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Interfaces\BranchRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpecialDOChargeController extends Controller
{
    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository,
    ) {}
    public function index()
    {
        return Inertia::render('Setting/SpecialDOCharge/SpecialDOChargesList');
    }

    public function create()
    {
        return Inertia::render('Setting/SpecialDOCharge/CreateSpecialDOCharge',[
        'cargoModes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'branches' => $this->branchRepository->getDestinationBranches(),
        ]);
    }

}
