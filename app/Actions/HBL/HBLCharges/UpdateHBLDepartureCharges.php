<?php

namespace App\Actions\HBL\HBLCharges;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLDepartureCharges
{
    use AsAction;

    /**
     * Actual payment data structure.
     *
     * $paymentData = [
     * 'freight_charge' => 0,
     * 'bill_charge' => 0,
     * 'other_charge' => 0,
     * 'destination_charge' => 0,
     * 'package_charges' => 0,
     * 'discount' => 0,
     * 'additional_charge' => 0,
     * 'grand_total' => 0,
     * 'paid_amount' => 0,
     * ];
     */
    public function handle(HBL $HBL, $paymentData = [])
    {
        $branchId = $HBL->branch_id;
        $currencyCode = $HBL->branch->currency_symbol;
        $currencyRateInLKR = $HBL->currency_rate;
        $isBranchPrepaid = $HBL->branch->is_prepaid;

        $freightCharge = $HBL->freight_charge;
        $billCharge = $HBL->bill_charge;
        $packageCharge = $HBL->package_charges;
        $discount = $paymentData['discount'] ?? 0;
        $additionalCharges = $paymentData['additional_charge'] ?? 0;
        $departureGrandTotal = $paymentData['grand_total'] ?? 0;

        $departureCharge = [
            'hbl_id' => $HBL->id,
            'branch_id' => $branchId,
            'base_currency_code' => $currencyCode,
            'base_currency_rate_in_lkr' => $currencyRateInLKR,
            'is_branch_prepaid' => $isBranchPrepaid,
            'freight_charge' => $freightCharge,
            'bill_charge' => $billCharge,
            'package_charge' => $packageCharge,
            'discount' => $discount,
            'additional_charges' => $additionalCharges,
            'departure_grand_total' => $departureGrandTotal,
        ];

        $departureChargeModel = $HBL->departureCharge;
        if ($departureChargeModel) {
            $departureChargeModel->update($departureCharge);
        } else {
            $departureChargeModel = $HBL->departureCharge()->create($departureCharge);
        }

        return $departureChargeModel;
    }
}
