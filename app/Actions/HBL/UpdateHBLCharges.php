<?php

namespace App\Actions\HBL;

use App\Actions\HBL\Warehouse\GetHBLDestinationTotalConvertedCurrency;
use App\Models\HBL;
use App\Models\Tax;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLCharges
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
        $applicableTaxes = Tax::whereIsActive(true)->get();

        $freightCharge = $HBL->freight_charge;
        $billCharge = $HBL->bill_charge;
        $packageCharge = 0;

        $departure1Total = $freightCharge + $billCharge + $packageCharge;

        $cargoType = $HBL->cargo_type;
        $packageListLength = $HBL->packages->count();
        $grandTotalVolume = $HBL->grand_total_volume;
        $grandTotalWeight = $HBL->grand_total_weight;
        $destinationCharge = GetHBLDestinationTotalConvertedCurrency::run($cargoType, $packageListLength, $grandTotalVolume, $grandTotalWeight);

        $destinationHandlingCharge = $destinationCharge['handlingCharges'];
        $destinationSLPACharge = $destinationCharge['slpaCharge'];
        $destinationBondCharge = $destinationCharge['bondCharge'];
        $destination1Total = $destinationCharge['totalAmount'];
        $destination1Tax = $destinationCharge['totalTax'];
        $destination1TotalWithTax = $destinationCharge['totalAmountWithTax'];

        $destinationDOCharge = null;
        $destinationDemurrageCharge = null;
        $destinationStampCharge = null;
        $destinationOtherCharge = null;
        $destination2Total = null;
        $destination2Tax = null;
        $destination2TotalWithTax = null;

        $departureTotalCharge = $isBranchPrepaid ? $departure1Total + $destination1TotalWithTax : $departure1Total;
        $departureDiscount = $paymentData['discount'] ?? 0;
        $departureAdditionalCharge = $paymentData['additional_charge'] ?? 0;
        $departureNetTotal = $departureTotalCharge - $departureDiscount + $departureAdditionalCharge;
        $departurePaidAmount = $paymentData['paid_amount'] ?? 0;
        $departureDue = $departureNetTotal - $departurePaidAmount;

        $destinationTotalCharge = $isBranchPrepaid ? $destination2TotalWithTax : $destination1TotalWithTax + $destination2TotalWithTax;
        $destinationDiscount = null;
        $destinationAdditionalCharge = null;

        $destinationTotalTax = null;
        $destinationNetTotal = null;
        $destinationPaidAmount = null;
        $destinationDue = null;

        $grandTotalCharge = null;
        $grandTotalPaid = null;
        $grandTotalDue = null;
    }
}
