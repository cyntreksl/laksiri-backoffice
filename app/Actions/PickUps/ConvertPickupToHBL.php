<?php

namespace App\Actions\PickUps;

use App\Actions\Branch\GetBranchByName;
use App\Actions\HBL\CreateHBLPackages;
use App\Actions\HBL\GenerateCRNumber;
use App\Actions\HBL\GenerateHBLNumber;
use App\Actions\HBL\UpdateOrCreateHBL;
use App\Actions\User\GetUserCurrentBranchID;
use App\Enum\PickupStatus;
use App\Models\HBL;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ConvertPickupToHBL
{
    use AsAction;

    public function handle($pickup, $request): HBL
    {
        $hbl_type = $request->hbl_type === 'D2D'
            ? 'Door to Door'
            : ($request->hbl_type ?? $pickup->hbl_type);

        $warehouse = GetBranchByName::run($request->warehouse);
        $data = [
            'cargo_type' => $request->cargo_type ?: $pickup->cargo_type,
            'hbl_type' => $hbl_type,
            'hbl_name' => $request->hbl_name ?: $pickup->name,
            'email' => $request->email ?: $pickup->email,
            'contact_number' => $request->contact_number ?: $pickup->contact_number,
            'whatsapp_number' => $request->whatsapp_number ?: $pickup->whatsapp_number,
            'additional_mobile_number' => $request->additional_mobile_number,
            'nic' => $request->nic,
            'iq_number' => $request->iq_number,
            'address' => $request->address ?: $pickup->address,
            'consignee_name' => $request->consignee_name,
            'consignee_nic' => $request->consignee_nic,
            'consignee_contact' => $request->consignee_contact,
            'consignee_whatsapp_number' => $request->consignee_whatsapp_number,
            'consignee_additional_mobile_number' => $request->consignee_additional_mobile_number,
            'consignee_address' => $request->consignee_address,
            'consignee_note' => $request->consignee_note,
            'warehouse' => $request->warehouse,
            'warehouse_id' => $request->warehouse_id ?? $warehouse['id'],
            'freight_charge' => $request->freight_charge,
            'bill_charge' => $request->bill_charge,
            'other_charge' => $request->other_charge,
            'destination_charge' => $request->destination_charge,
            'discount' => $request->discount,
            'paid_amount' => $request->paid_amount,
            'grand_total' => $request->grand_total,
            'pickup_id' => $pickup->id,
            'system_status' => HBL::SYSTEM_STATUS_HBL_PREPARATION_BY_DRIVER,
            'packages' => $request->packages,
            'is_completed' => $request->is_completed,
            'hbl_number' => isset($request->is_completed) && $request->is_completed ? GenerateHBLNumber::run(GetUserCurrentBranchID::run()) : null,
            'cr_number' => GenerateCRNumber::run(),
            'is_departure_charges_paid' => $request->is_departure_charges_paid,
            'is_destination_charges_paid' => $request->is_destination_charges_paid,
        ];

        try {
            DB::beginTransaction();

            $hbl = UpdateOrCreateHBL::run($data);

            if (! empty($data['is_completed']) && $data['is_completed']) {
                $packagesData = $data['packages'];

                CreateHBLPackages::run($hbl, $packagesData);

                $hbl->addStatus('HBL Preparation by driver');

                $pickup->addStatus('Cargo collected by driver');
            }

            $pickup->update([
                'status' => PickupStatus::PROCESSING->value,
                'hbl_id' => $hbl->id,
            ]);

            DB::commit();

            return $hbl;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }
}
