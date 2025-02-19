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
use Inertia\Inertia;

class ThirdPartyAgentController extends Controller
{
    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository,
        private readonly SettingRepositoryInterface $settingRepository,
        private readonly CountryRepositoryInterface $countryRepository,
    ) {}

    /**
     * Display a listing of third-party agents.
     */
    public function index()
    {
        return Inertia::render('Agent/AgentList', [
            'agents' => $this->branchRepository->getBranchesByType(true), // Fetch only agents
        ]);
    }

    /**
     * Show the form for creating a new agent.
     */
    public function create()
    {
        return Inertia::render('Agent/CreateAgent', [
            'cargoModes' => CargoType::cases(),
            'deliveryTypes' => HBLType::cases(),
            'packageTypes' => PackageType::cases(),
            'branchTypes' => BranchType::cases(),
        ]);
    }

    /**
     * Store a newly created agent in storage.
     */
    public function store(StoreBranchRequest $request)
    {
        $data = $request->all();
        $data['is_third_party_agent'] = true; // Ensure it's marked as an agent

        // Dump the data


        // Create the branch
        $this->branchRepository->createBranch($data);
    }

    /**
     * Show the form for editing the specified agent.
     */
    public function edit(Branch $branch)
    {
        return Inertia::render('Agent/EditAgent', [
            'cargoModes' => CargoType::cases(),
            'deliveryTypes' => HBLType::cases(),
            'packageTypes' => PackageType::cases(),
            'branchTypes' => BranchType::cases(),
            'agent' => $branch,
            'settings' => $this->settingRepository->getSettings(),
            'countryCodes' => $this->countryRepository->getAllPhoneCodes(),
            'countryNames' => $this->countryRepository->getAllCountries(),
            'notificationTypes' => NotificationType::cases(),
        ]);
    }

    /**
     * Update the specified agent in storage.
     */
    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $data = $request->all();
        $data['is_third_party_agent'] = true; // Ensure it remains an agent

        $this->branchRepository->updateBranch($data, $branch);
    }

    /**
     * Remove the specified agent from storage (soft delete).
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();
    }
}
