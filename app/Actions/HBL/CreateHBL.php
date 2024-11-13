<?php

namespace App\Actions\HBL;

use App\Actions\HBL\CashSettlement\UpdateHBLPayments;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateHBL
{
    use AsAction;

    public function handle(array $data): HBL
    {
        $reference = GenerateHBLReferenceNumber::run();

        $hbl = HBL::create([
            'reference' => $reference,
            'branch_id' => GetUserCurrentBranchID::run(),
            'cargo_type' => $data['cargo_type'],
            'hbl_type' => $data['hbl_type'],
            'hbl' => $reference,
            'hbl_name' => $data['hbl_name'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'nic' => $data['nic'],
            'iq_number' => $data['iq_number'],
            'address' => $data['address'],
            'consignee_name' => $data['consignee_name'],
            'consignee_nic' => $data['consignee_nic'],
            'consignee_contact' => $data['consignee_contact'],
            'consignee_address' => $data['consignee_address'],
            'consignee_note' => $data['consignee_note'],
            'warehouse' => $data['warehouse'],
            'freight_charge' => $data['freight_charge'],
            'bill_charge' => $data['bill_charge'],
            'other_charge' => $data['other_charge'],
            'discount' => $data['discount'],
            'additional_charge' => $data['additional_charge'],
            'paid_amount' => $data['paid_amount'],
            'grand_total' => $data['grand_total'],
            'created_by' => auth()->id(),
            'pickup_id' => $data['pickup_id'] ?? null,
            'hbl_number' => GenerateHBLNumber::run(GetUserCurrentBranchID::run()),
            'cr_number' => GenerateCRNumber::run(),
            'system_status' => $data['system_status'] ?? HBL::SYSTEM_STATUS_HBL_PREPARATION_BY_WAREHOUSE,
        ]);

        if (isset($data['paid_amount'])) {
            UpdateHBLPayments::run($data['paid_amount'], $hbl);
        }

        return $hbl;
    }
}
