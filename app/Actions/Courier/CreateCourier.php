<?php

namespace App\Actions\Courier;

use App\Actions\HBL\GenerateHBLReferenceNumber;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Courier;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCourier
{
    use AsAction;
    public function handle( array $data)
    {
        $reference = GenerateHBLReferenceNumber::run();
        $courier = Courier::create([
            'reference_number' => $reference,
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
            'paid_amount' => $data['paid_amount'],
            'discount' => $data['discount'],
            'total_amount' => $data['total_amount'],
            'status' => $data['status'],
            'system_status' => $data['system_status'],
        ]);
        return $courier;
    }


}
