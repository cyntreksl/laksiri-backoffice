<?php

namespace App\Actions\HBL\CashSettlement;

use App\Actions\Branch\GetBranchById;
use App\Actions\HBL\GetHBLDestinationTotalSummary;
use App\Actions\HBL\GetHBLTotalSummary;
use App\Actions\Tax\GetTaxesByWarehouse;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLPayments
{
    use AsAction;

    public function handle(array $data, HBL $hbl): void
    {
        $this->updateOrCreateHblPayment($data, $hbl);
        $this->updateHblPaidAmount($hbl, $data['paid_amount']);
    }

    protected function updateOrCreateHblPayment(array $data, HBL $hbl): void
    {
        $paymentData = $this->prepareHblPaymentData($data, $hbl);

        $hbl->hblPayment()->withoutGlobalScopes()->updateOrCreate(
            ['hbl_id' => $hbl->id],
            $paymentData
        );
    }

    protected function prepareHblPaymentData(array $data, HBL $hbl): array
    {
        $existingPayment = $hbl->hblPayment()->withoutGlobalScopes()->first();

        $departurePayments = GetHBLTotalSummary::run($hbl);

        $destinationPayments = GetHBLDestinationTotalSummary::run($hbl);

        $currentBranch = GetBranchById::run(GetUserCurrentBranchID::run());

        // Calculate a sum of all charges
        $chargesTotal =
            ($departurePayments['freight_charge'] ?? $existingPayment->freight_charge ?? 0) +
            ($departurePayments['bill_charge'] ?? $existingPayment->bill_charge ?? 0) +
            ($departurePayments['package_charges'] ?? $existingPayment->other_charge ?? 0) +
            ($departurePayments['destination_charges'] ?? $existingPayment->destination_charge ?? 0) +
            ($data['additional_charge'] ?? $existingPayment->additional_charge ?? 0);

        // Calculate grand total
        $grandTotal = $chargesTotal
            + ($departurePayments['vat'] ?? 0)
            - ($departurePayments['discount'] ?? $existingPayment->discount ?? 0);

        return [
            'branch_id' => GetUserCurrentBranchID::run(),

            'base_currency' => $currentBranch->currency_name ?? null,
            'currency_code' => $currentBranch->currency_symbol ?? null,

            'freight_charge' => $departurePayments['freight_charge'] ?? $existingPayment->freight_charge ?? 0,
            'bill_charge' => $departurePayments['bill_charge'] ?? $existingPayment->bill_charge ?? 0,
            'other_charge' => $departurePayments['other_charge'] ?? $existingPayment->other_charge ?? 0,
            'destination_charge' => $departurePayments['destination_charges'] ?? $existingPayment->destination_charge ?? 0,
            'additional_charge' => $data['additional_charge'] ?? $existingPayment->additional_charge ?? 0,
            'package_charge' => $departurePayments['package_charges'] ?? 0,
            'sub_total' => $chargesTotal ?? 0,

            'discount' => $departurePayments['discount'] ?? $existingPayment->discount ?? 0,

            'do_charge' => $destinationPayments['dOCharge'] ?? 0,
            'handling_charge' => $destinationPayments['handlingCharges'] ?? 0,
            'slpa_charge' => $destinationPayments['slpaCharge'] ?? 0,
            'bond_charge' => $destinationPayments['bondCharge'] ?? 0,
            'demurrage_charge' => $destinationPayments['demurrageCharge'] ?? 0,
            'destination_total' => $destinationPayments['totalAmount'] ?? 0,
            'tax' => $departurePayments['vat'] ?? 0,
            'tax_rates' => GetTaxesByWarehouse::run($hbl->warehouse_id),

            'is_departure_charges_paid' => $data['is_departure_charges_paid'] ?? $existingPayment->is_departure_charges_paid ?? false,
            'is_destination_charges_paid' => $data['is_destination_charges_paid'] ?? $existingPayment->is_destination_charges_paid ?? false,

            'paid_amount' => $this->calculatePaidAmount($data, $hbl),

            'grand_total' => $grandTotal,

            'status' => GetHBLPaymentStatus::run(
                $data['paid_amount'] ?? $existingPayment->paid_amount ?? 0,
                $grandTotal
            ),

            'created_by' => auth()->id(),
        ];
    }

    protected function calculatePaidAmount(array $data, HBL $hbl): float
    {
        if ($hbl->hblPayment()->withoutGlobalScopes()->get()->isEmpty()) {
            return $data['paid_amount'];
        }

        return round($data['paid_amount'] - $hbl->paid_amount, 2);
    }

    protected function updateHblPaidAmount(HBL $hbl, float $paidAmount): void
    {
        $hbl->update(['paid_amount' => $paidAmount]);
    }
}
