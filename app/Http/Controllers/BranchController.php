<?php

namespace App\Http\Controllers;

use App\Enum\BranchType;
use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Enum\NotificationType;
use App\Enum\PackageType;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\SettingRepositoryInterface;
use App\Models\Branch;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class BranchController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository,
        private readonly SettingRepositoryInterface $settingRepository,
        private readonly CountryRepositoryInterface $countryRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('branches.list');

        return Inertia::render('Branch/BranchList', [
            'branches' => $this->branchRepository->getBranches(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('branches.create');

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
        $this->authorize('branches.edit');

        return Inertia::render('Branch/EditBranch', [
            'cargoModes' => CargoType::cases(),
            'deliveryTypes' => HBLType::cases(),
            'packageTypes' => PackageType::cases(),
            'branchTypes' => BranchType::cases(),
            'branch' => $branch,
            'settings' => $this->settingRepository->getSettings(),
            'countryCodes' => $this->countryRepository->getAllPhoneCodes(),
            'countryNames' => $this->countryRepository->getAllCountries(),
            'notificationTypes' => NotificationType::cases(),
            'timezones' => Branch::timezones(),
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
        $this->authorize('branches.delete');
    }
}
