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
        return [
            'branch_id' => GetUserCurrentBranchID::run(),
            'freight_charge' => $data['freight_charge'],
            'bill_charge' => $data['bill_charge'],
            'other_charge' => $data['other_charge'],
            'destination_charge' => $data['destination_charge'],
            'discount' => $data['discount'],
            'additional_charge' => $data['additional_charge'],
            'do_charge' => $data['do_charge'],
            'is_departure_charges_paid' => $data['is_departure_charges_paid'],
            'is_destination_charges_paid' => $data['is_destination_charges_paid'],
            'grand_total' => $data['grand_total'],
            'paid_amount' => $this->calculatePaidAmount($data, $hbl),
            'status' => GetHBLPaymentStatus::run($data['paid_amount'], $hbl->grand_total),
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
