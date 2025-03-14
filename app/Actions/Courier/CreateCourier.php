<?php

namespace App\Actions\Courier;

use App\Actions\HBL\GenerateCourierNumber;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Courier;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCourier
{
    use AsAction;

    public function handle(array $data)
    {
        $courier = Courier::create([
            'branch_id' => GetUserCurrentBranchID::run(),
            'cargo_type' => $data['cargo_type'],
            'hbl_type' => $data['hbl_type'],
            'courier_agent' => $data['courier_agent'],
            'courier_number' => GenerateCourierNumber::run(GetUserCurrentBranchID::run()),
            'name' => $data['name'],
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
            'status' => $data['status'],
            'created_by' => auth()->id(),
        ]);

        return $courier;
    }
}
