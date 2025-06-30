<?php

namespace App\Actions\HBL\HBLCharges;

use App\Actions\HBL\Warehouse\GetHBLDestinationTotalConvertedCurrency;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLDestinationCharges
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

        $cargoType = $HBL->cargo_type;
        $packageListLength = $HBL->packages->count();
        $grandTotalVolume = $HBL->packages()->sum('volume');
        $grandTotalWeight = $HBL->packages()->sum('weight');
        $destinationCharge = GetHBLDestinationTotalConvertedCurrency::run($cargoType, $packageListLength, $grandTotalVolume, $grandTotalWeight);

        $destinationHandlingCharge = $destinationCharge['handlingCharges'];
        $destinationSLPACharge = $destinationCharge['slpaCharge'];
        $destinationBondCharge = $destinationCharge['bondCharge'];
        $destination1Total = $destinationCharge['totalAmount'];
        $destination1Tax = $destinationCharge['totalTax'];
        $destination1TotalWithTax = $destinationCharge['totalAmountWithTax'];

        $doCalculation = CalculateDoCharge::run($HBL);
        $demurrageCalculation = CalculateDemurrageCharge::run($HBL);

        $doCharge = $doCalculation['amount'] ?? 0.00;
        $demurrageCharge = $demurrageCalculation['rate'] ?? 0.00;
        $destination2Total = $doCharge + $demurrageCharge;

        $d2Tax = CalculateTax::run($destination2Total);
        $destination2Tax = $d2Tax['total_tax'];
        $destination2TotalWithTax = $d2Tax['amount_with_tax'];
        $stampCharge = $destination2TotalWithTax > 25000 ? 25 : 0;

        $destinationCharge = [
            'branch_id' => $branchId,
            'base_currency_code' => $currencyCode,
            'base_currency_rate_in_lkr' => $currencyRateInLKR,
            'is_branch_prepaid' => $isBranchPrepaid,
            'destination_handling_charge' => $destinationHandlingCharge,
            'destination_slpa_charge' => $destinationSLPACharge,
            'destination_bond_charge' => $destinationBondCharge,
            'destination_1_total' => $destination1Total,
            'destination_1_tax' => $destination1Tax,
            'destination_1_total_with_tax' => $destination1TotalWithTax,
            'destination_do_charge' => $doCharge,
            'destination_demurrage_charge' => $demurrageCharge,
            'destination_stamp_charge' => $stampCharge,
            'destination_other_charge' => null,
            'destination_2_total' => $destination2Total,
            'destination_2_tax' => $destination2Tax,
            'destination_2_total_with_tax' => $destination2TotalWithTax,
            'stop_demurrage_at' => null,
        ];

        $destinationChargeModel = $HBL->destinationCharge;
        if ($destinationChargeModel) {
            $destinationChargeModel->update($destinationCharge);
        } else {
            $destinationChargeModel = $HBL->destinationCharge()->create($destinationCharge);
        }

        return $destinationChargeModel;
    }
}
