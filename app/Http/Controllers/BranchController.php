<?php

namespace App\Http\Controllers;

use App\Enum\BranchType;
use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Enum\PackageType;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Interfaces\BranchRepositoryInterface;
use App\Models\Branch;
use Inertia\Inertia;

class BranchController extends Controller
{
    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Branch/BranchList', [
            'branches' => $this->branchRepository->getBranches(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Branch/CreateBranch', [
            'cargoModes' => CargoType::cases(),
            'deliveryTypes' => HBLType::cases(),
            'packageTypes' => PackageType::cases(),
            'branchTypes' => BranchType::cases(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBranchRequest $request)
    {
        $this->branchRepository->createBranch($request->all());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        return Inertia::render('Branch/EditBranch', [
            'cargoModes' => CargoType::cases(),
            'deliveryTypes' => HBLType::cases(),
            'packageTypes' => PackageType::cases(),
            'branchTypes' => BranchType::cases(),
            'branch' => $branch,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $this->branchRepository->updateBranch($request->all(), $branch);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        //
    }
}
