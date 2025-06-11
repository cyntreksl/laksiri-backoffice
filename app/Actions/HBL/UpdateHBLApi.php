<?php

namespace App\Actions\HBL;

use App\Actions\HBL\CashSettlement\UpdateHBLPayments;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLApi
{
    use AsAction;

    public function handle(HBL $hbl, array $data): HBL
    {
        $hbl->update([
            'cargo_type' => $data['cargo_type'],
            'consignee_nic' => $data['consignee_nic'],
            'consignee_contact' => $data['consignee_contact'],
            'consignee_address' => $data['consignee_address'],
            'consignee_note' => $data['consignee_note'],
            'warehouse' => strtoupper($data['warehouse']),
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'freight_charge' => $data['freight_charge'],
            'bill_charge' => $data['bill_charge'],
            'other_charge' => $data['other_charge'],
            'discount' => $data['discount'],
            'paid_amount' => $data['paid_amount'],
            'grand_total' => $data['grand_total'],
            'is_completed' => $data['is_completed'] ?? false,
        ]);

        if (! empty($data['paid_amount'])) {
            UpdateHBLPayments::run($data, $hbl);
        }

        return $hbl;
    }
}
