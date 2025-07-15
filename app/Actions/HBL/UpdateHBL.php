<?php

namespace App\Actions\HBL;

use App\Actions\HBL\CashSettlement\UpdateHBLPayments;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBL
{
    use AsAction;

    public function handle(HBL $hbl, array $data): HBL
    {
        $hbl->update([
            'cargo_type' => $data['cargo_type'],
            'hbl_type' => $data['hbl_type'],
            'hbl_name' => $data['hbl_name'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'additional_mobile_number' => $data['additional_mobile_number'],
            'whatsapp_number' => $data['whatsapp_number'],
            'nic' => $data['nic'],
            'iq_number' => $data['iq_number'],
            'address' => $data['address'],
            'consignee_name' => $data['consignee_name'],
            'consignee_nic' => $data['consignee_nic'],
            'consignee_contact' => $data['consignee_contact'],
            'consignee_additional_mobile_number' => $data['consignee_additional_mobile_number'],
            'consignee_whatsapp_number' => $data['consignee_whatsapp_number'],
            'consignee_address' => $data['consignee_address'],
            'consignee_note' => $data['consignee_note'],
            'warehouse' => strtoupper($data['warehouse']),
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'freight_charge' => $data['freight_charge'],
            'bill_charge' => $data['bill_charge'],
            'other_charge' => $data['other_charge'],
            'destination_charge' => $data['destination_charge'],
            'discount' => $data['discount'],
            'additional_charge' => $data['additional_charge'],
            'paid_amount' => $data['paid_amount'],
            'grand_total' => $data['grand_total'],
            'pickup_id' => $data['pickup_id'] ?? null,
            'is_departure_charges_paid' => $data['is_departure_charges_paid'],
            'is_destination_charges_paid' => $data['is_destination_charges_paid'],
            'is_arrived_to_primary_warehouse' => $data['is_arrived_to_primary_warehouse'] ?? false,
        ]);

        if (! empty($data['paid_amount'])) {
            UpdateHBLPayments::run($data, $hbl);
        }

        return $hbl;
    }
}
