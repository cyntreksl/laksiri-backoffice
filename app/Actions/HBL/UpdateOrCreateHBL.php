<?php

namespace App\Actions\HBL;

use App\Actions\HBL\CashSettlement\UpdateHBLPayments;
use App\Actions\User\GetUserCurrentBranchID;
use App\Enum\PickupStatus;
use App\Models\HBL;
use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateOrCreateHBL
{
    use AsAction;

    public function handle(array $data): HBL
    {
        $status = 'draft';
        $reference = null;

        if (isset($data['is_completed'])) {
            $reference = GenerateHBLReferenceNumber::run();
            $status = 'completed';

            if (isset($data['pickup_id'])) {
                $pickup = Pickup::find($data['pickup_id']);

                if ($pickup) {
                    $pickup->update([
                        'status' => PickupStatus::COLLECTED->value,
                        'system_status' => PickUp::SYSTEM_STATUS_CARGO_COLLECTED,
                    ]);
                }
            }
        }

        $hbl = HBL::updateOrCreate([
            'consignee_contact' => $data['consignee_contact'],
            'pickup_id' => $data['pickup_id'],
        ], [
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
            'paid_amount' => $data['paid_amount'],
            'grand_total' => $data['grand_total'],
            'created_by' => auth()->id(),
            'hbl_number' => $data['hbl_number'],
            'cr_number' => $data['cr_number'],
            'pickup_id' => $data['pickup_id'] ?? null,
            'system_status' => $data['system_status'] ?? HBL::SYSTEM_STATUS_HBL_PREPARATION_BY_WAREHOUSE,
            'status' => $status,
        ]);

        if (isset($data['paid_amount']) && isset($data['is_completed'])) {
            UpdateHBLPayments::run($data['paid_amount'], $hbl);
        }

        return $hbl;
    }
}
