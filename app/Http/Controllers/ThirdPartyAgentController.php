<?php

namespace App\Http\Controllers;

use App\Enum\BranchType;
use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Enum\NotificationType;
use App\Enum\PackageType;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateAgentRequest;
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
//        private readonly SettingRepositoryInterface $settingRepository,
//        private readonly CountryRepositoryInterface $countryRepository,
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
    public function store(StoreAgentRequest $request)
    {
        $data = $request->all();
        $data['is_third_party_agent'] = true;

        $this->branchRepository->createAgent($data);
    }

    /**
     * Show the form for editing the specified agent.
     */
    public function edit($id)
    {
        $branch = Branch::find($id);

        return Inertia::render('Agent/EditAgent', [
            'cargoModes' => CargoType::cases(),
            'deliveryTypes' => HBLType::cases(),
            'packageTypes' => PackageType::cases(),
            'branchTypes' => BranchType::cases(),
            'agent' => $branch,
        ]);
    }

    /**
     * Update the specified agent in storage.
     */
    public function update(UpdateAgentRequest $request,  $branch)
    {
        $branch = Branch::find($branch);
        $data = $request->all();
        $data['is_third_party_agent'] = true; // Ensure it remains an agent

        $this->branchRepository->updateAgent($data, $branch);
    }

    /**
     * Remove the specified agent from storage (soft delete).
     */
    public function destroy(Branch $branch)
    {
        $branch->delete();
    }
}
