<?php

namespace App\Actions\Cashier;

use App\Models\CashierHBLPayment;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCashierHBLPayments
{
    use AsAction;

    public function handle(array $data, HBL $hbl, float|string $paid_amount)
    {
        // Determine if this is a verification (already paid, cashier is just verifying)
        $isVerification = $paid_amount == 0;

        // Set verified_at when:
        // 1. It's a verification (paid_amount == 0) - already paid elsewhere, cashier is confirming
        // 2. OR when a payment is made (paid_amount > 0) - cashier is processing payment and confirming
        $shouldVerify = $isVerification || $paid_amount > 0;

        // Load departure and destination charges with HBL
        $hbl->load(['departureCharge', 'destinationCharge']);

        // Prepare individual departure charge columns
        $departureCharges = [];
        if ($hbl->departureCharge) {
            $departureCharges = [
                'departure_freight_charge' => $hbl->departureCharge->freight_charge,
                'departure_bill_charge' => $hbl->departureCharge->bill_charge,
                'departure_package_charge' => $hbl->departureCharge->package_charge,
                'departure_discount' => $hbl->departureCharge->discount,
                'departure_additional_charges' => $hbl->departureCharge->additional_charges,
                'departure_grand_total' => $hbl->departureCharge->departure_grand_total,
                'departure_base_currency_code' => $hbl->departureCharge->base_currency_code,
                'departure_base_currency_rate_in_lkr' => $hbl->departureCharge->base_currency_rate_in_lkr,
                'departure_is_branch_prepaid' => $hbl->departureCharge->is_branch_prepaid,
            ];
        }

        // Prepare individual destination charge columns
        $destinationCharges = [];
        if ($hbl->destinationCharge) {
            $destinationCharges = [
                'destination_handling_charge' => $hbl->destinationCharge->destination_handling_charge,
                'destination_slpa_charge' => $hbl->destinationCharge->destination_slpa_charge,
                'destination_bond_charge' => $hbl->destinationCharge->destination_bond_charge,
                'destination_1_total' => $hbl->destinationCharge->destination_1_total,
                'destination_1_tax' => $hbl->destinationCharge->destination_1_tax,
                'destination_1_total_with_tax' => $hbl->destinationCharge->destination_1_total_with_tax,
                'destination_do_charge' => $hbl->destinationCharge->destination_do_charge,
                'destination_demurrage_charge' => $hbl->destinationCharge->destination_demurrage_charge,
                'destination_stamp_charge' => $hbl->destinationCharge->destination_stamp_charge,
                'destination_other_charge' => $hbl->destinationCharge->destination_other_charge,
                'destination_2_total' => $hbl->destinationCharge->destination_2_total,
                'destination_2_tax' => $hbl->destinationCharge->destination_2_tax,
                'destination_2_total_with_tax' => $hbl->destinationCharge->destination_2_total_with_tax,
                'destination_base_currency_code' => $hbl->destinationCharge->base_currency_code,
                'destination_base_currency_rate_in_lkr' => $hbl->destinationCharge->base_currency_rate_in_lkr,
                'destination_is_branch_prepaid' => $hbl->destinationCharge->is_branch_prepaid,
                'destination_applicable_taxes' => $hbl->destinationCharge->applicable_taxes,
                'destination_stop_demurrage_at' => $hbl->destinationCharge->stop_demurrage_at,
            ];
        }

        CashierHBLPayment::create(array_merge([
            'verified_by' => auth()->id(),
            'customer_queue_id' => $data['customer_queue']['id'],
            'token_id' => $data['customer_queue']['token_id'],
            'hbl_id' => $hbl->id,
            'paid_amount' => $paid_amount,
            'note' => $data['note'],
            'verified_at' => $shouldVerify ? now() : null,
            'additional_charges' => $data['additional_charges'] ?? 0,
            'discount' => $data['discount'] ?? 0,
        ], $departureCharges, $destinationCharges));
    }
}
