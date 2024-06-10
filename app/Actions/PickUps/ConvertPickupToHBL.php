<?php

namespace App\Actions\PickUps;

use App\Actions\HBL\CreateHBL;
use App\Enum\PickupStatus;
use App\Models\HBL;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ConvertPickupToHBL
{
    use AsAction;

    public function handle($pickUp, $request): HBL
    {
        $data = [
            'cargo_type' => $pickUp->cargo_type,
            'hbl_type' => $request->hbl_type,
            'hbl_name' => $pickUp->name,
            'email' => $pickUp->email,
            'contact_number' => $pickUp->contact_number,
            'nic' => $request->nic,
            'iq_number' => $request->iq_number,
            'address' => $pickUp->address,
            'consignee_name' => $request->consignee_name,
            'consignee_nic' => $request->consignee_nic,
            'consignee_contact' => $request->consignee_contact,
            'consignee_address' => $request->consignee_address,
            'consignee_note' => $request->consignee_note,
            'warehouse' => $request->warehouse,
            'freight_charge' => $request->freight_charge,
            'bill_charge' => $request->bill_charge,
            'other_charge' => $request->other_charge,
            'discount' => $request->discount,
            'paid_amount' => $request->paid_amount,
            'grand_total' => $request->grand_total,
            'pickup_id' => $pickUp->id,
            'system_status' => 3.1,
        ];

        try {
            DB::beginTransaction();
            $hbl = CreateHBL::run($data);
            $pickUp->update([
                'status' => PickupStatus::COLLECTED->value,
                'hbl_id' => $hbl->id,
                'system_status' => 3,
            ]);
            DB::commit();

            return $hbl;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }
}
