<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Http\Requests\StoreBranchPriceRequest;
use App\Http\Requests\UpdateBranchPriceRequest;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\PriceRepositoryInterface;
use App\Models\BranchPrice;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PriceController extends Controller
{
    public function __construct(
        private readonly PriceRepositoryInterface $priceRepository,
        private readonly BranchRepositoryInterface $branchRepository,
    ) {}

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
            'hblTypes' => HBLType::cases(),
            'branches' => $this->branchRepository->getDestinationBranches(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBranchPriceRequest $request)
    {
        $this->validatePriceRules($request->all());

        $this->priceRepository->createPriceRule($request->all());
    }

    private function validatePriceRules(array $data)
    {
        // Check if this combination exists in a database
        $existingRules = BranchPrice::where([
            'destination_branch_id' => $data['destination_branch_id'],
            'cargo_mode' => $data['cargo_mode'],
            'hbl_type' => $data['hbl_type'],
            'price_mode' => $data['price_mode'],
        ])->get();

        // If no existing rules, the first rule must be ">0"
        if ($existingRules->isEmpty() && $data['priceRules'][0]['condition'] !== '>0') {
            throw ValidationException::withMessages([
                'priceRules' => 'The first condition for a new combination must be ">0"',
            ]);
        }

        // If existing rules exist, check if the "> 0" condition is being added again
        if ($existingRules->isNotEmpty()) {
            $hasGtZeroInDb = $existingRules->contains('condition', '>0');
            $hasGtZeroInRequest = collect($data['priceRules'])->contains('condition', '>0');

            if ($hasGtZeroInDb && $hasGtZeroInRequest) {
                throw ValidationException::withMessages([
                    'priceRules' => 'A ">0" condition already exists for this combination',
                ]);
            }
        }

        // Check for duplicate conditions against a database
        $existingConditions = $existingRules->pluck('condition')->toArray();
        $newConditions = array_column($data['priceRules'], 'condition');

        $duplicates = array_intersect($existingConditions, $newConditions);
        if (! empty($duplicates)) {
            throw ValidationException::withMessages([
                'priceRules' => 'The following conditions already exist: '.implode(', ', $duplicates),
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($branchPrice)
    {
        $branchPrice = BranchPrice::find($branchPrice);

        return Inertia::render('Setting/Pricing/EditPriceRule', [
            'cargoModes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
            'branches' => $this->branchRepository->getDestinationBranches(),
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
