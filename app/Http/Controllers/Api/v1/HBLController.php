<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\HBL\GetHBLById;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHBLRequest;
use App\Interfaces\Api\HBLRepositoryInterface;
use App\Models\HBL;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HBLController extends Controller
{
    use ResponseAPI;

    public function __construct(
        private readonly HBLRepositoryInterface $HBLRepository,
    ) {}

    /**
     * Show HBL
     *
     * Display the specified HBL.
     *
     * @group HBL
     */
    public function show(HBL $hbl): JsonResponse
    {
        return $this->HBLRepository->showHBL($hbl);
    }

    /**
     * Store HBL
     *
     * Store a newly created HBL in storage.
     *
     * @group HBL
     */
    public function store(StoreHBLRequest $request)
    {
        return $this->HBLRepository->storeHBL($request->all());
    }

    /**
     * Calculate Payments
     *
     * Calculate HBL Package Payments.
     *
     * @group HBL
     */
    public function calculatePayment(Request $request)
    {
        return $this->HBLRepository->calculatePayment($request->all());
    }

    public function getHBLRules(Request $request)
    {
        return $this->HBLRepository->getHBLRules($request->all());
    }

    public function completedHBL(Request $request)
    {
        return $this->HBLRepository->getCompletedHBL($request->all());
    }

    public function completedHBLView(Request $request, $id)
    {
        $hbl = HBL::find($id);
        if (! $hbl) {
            return $this->error('HBL not found');
        }

        return $this->HBLRepository->completedHBLView($hbl);
    }

    public function update(Request $request, HBL $hbl): JsonResponse
    {
        return $this->HBLRepository->updateHBL($hbl, $request->all());
    }

    public function getHBLTotalSummary(HBL $hbl)
    {
        return $this->HBLRepository->getHBLTotalSummary($hbl);
    }

    public function getHBLDestinationTotalSummary(HBL $hbl)
    {
        return $this->HBLRepository->getHBLDestinationTotalSummary($hbl);
    }

    public function hblChargeDetails(Request $request)
    {
        $id = $request->id;
        $hbl = GetHBLById::run($id);

        $hbl->load([
            'departureCharge',
            'destinationCharge',
        ]);

        $chargeDetails = [
            'base_currency_code' => $hbl->departureCharge->base_currency_code ?? null,
            'base_currency_rate_in_lkr' => $hbl->departureCharge->base_currency_rate_in_lkr ?? null,
            'is_branch_prepaid' => $hbl->departureCharge->is_branch_prepaid ?? null,
            'freight_charge' => $hbl->departureCharge->freight_charge ?? null,
            'bill_charge' => $hbl->departureCharge->bill_charge ?? null,
            'package_charge' => $hbl->departureCharge->package_charge ?? null,
            'discount' => $hbl->departureCharge->discount ?? null,
            'additional_charges' => $hbl->departureCharge->additional_charges ?? null,
            'departure_grand_total' => $hbl->departureCharge->departure_grand_total ?? null,

            'destination_handling_charge' => $hbl->destinationCharge->destination_handling_charge ?? null,
            'destination_slpa_charge' => $hbl->destinationCharge->destination_slpa_charge ?? null,
            'destination_bond_charge' => $hbl->destinationCharge->destination_bond_charge ?? null,
            'destination_1_total' => $hbl->destinationCharge->destination_1_total ?? null,
            'destination_1_tax' => $hbl->destinationCharge->destination_1_tax ?? null,
            'destination_1_total_with_tax' => $hbl->destinationCharge->destination_1_total_with_tax ?? null,

            'destination_do_charge' => $hbl->destinationCharge->destination_do_charge ?? null,
            'destination_demurrage_charge' => $hbl->destinationCharge->destination_demurrage_charge ?? null,
            'destination_stamp_charge' => $hbl->destinationCharge->destination_stamp_charge ?? null,
            'destination_other_charge' => $hbl->destinationCharge->destination_other_charge ?? null,
            'destination_2_total' => $hbl->destinationCharge->destination_2_total ?? null,
            'destination_2_tax' => $hbl->destinationCharge->destination_2_tax ?? null,
            'destination_2_total_with_tax' => $hbl->destinationCharge->destination_2_total_with_tax ?? null,
            'stop_demurrage_at' => $hbl->destinationCharge->stop_demurrage_at ?? null,
        ];

        return $this->success('HBL Charge Details', $chargeDetails);

    }
}
