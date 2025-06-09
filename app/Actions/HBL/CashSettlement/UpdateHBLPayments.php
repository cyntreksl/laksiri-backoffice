<?php

namespace App\Actions\HBL\CashSettlement;

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

        return [
            'branch_id' => GetUserCurrentBranchID::run(),
            'freight_charge' => $data['freight_charge'] ?? $existingPayment->freight_charge ?? 0,
            'bill_charge' => $data['bill_charge'] ?? $existingPayment->bill_charge ?? 0,
            'other_charge' => $data['other_charge'] ?? $existingPayment->other_charge ?? 0,
            'destination_charge' => $data['destination_charge'] ?? $existingPayment->destination_charge ?? 0,
            'discount' => $data['discount'] ?? $existingPayment->discount ?? 0,
            'additional_charge' => $data['additional_charge'] ?? $existingPayment->additional_charge ?? 0,
            'do_charge' => $data['do_charge'] ?? $existingPayment->do_charge ?? 0,
            'is_departure_charges_paid' => $data['is_departure_charges_paid'] ?? $existingPayment->is_departure_charges_paid ?? false,
            'is_destination_charges_paid' => $data['is_destination_charges_paid'] ?? $existingPayment->is_destination_charges_paid ?? false,
            'grand_total' => $data['grand_total'] ?? $existingPayment->grand_total ?? 0,
            'paid_amount' => $this->calculatePaidAmount($data, $hbl),
            'status' => GetHBLPaymentStatus::run(
                $data['paid_amount'] ?? $existingPayment->paid_amount ?? 0,
                $hbl->grand_total
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
