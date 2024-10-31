<?php

namespace App\Actions\PickUps;

use App\Actions\HBL\CreateHBLPackages;
use App\Actions\HBL\GenerateHBLNumber;
use App\Actions\HBL\UpdateOrCreateHBL;
use App\Actions\User\GetUserCurrentBranch;
use App\Enum\PickupStatus;
use App\Models\HBL;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class ConvertPickupToHBL
{
    use AsAction;

    public function handle($pickup, $request): HBL
    {
        $currentBranch = GetUserCurrentBranch::run();

        $data = [
            'cargo_type' => $pickup->cargo_type,
            'hbl_type' => $request->hbl_type,
            'hbl_name' => $pickup->name,
            'email' => $pickup->email,
            'contact_number' => $pickup->contact_number,
            'nic' => $request->nic,
            'iq_number' => $request->iq_number,
            'address' => $pickup->address,
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
            'pickup_id' => $pickup->id,
            'system_status' => HBL::SYSTEM_STATUS_HBL_PREPARATION_BY_DRIVER,
            'packages' => $request->packages,
            'is_completed' => $request->is_completed,
            'hbl_number' => GenerateHBLNumber::run($currentBranch['branchName']),
            'cr_number' => GenerateHBLNumber::run($currentBranch['branchName']),
        ];

        try {
            DB::beginTransaction();

            $hbl = UpdateOrCreateHBL::run($data);

            if (! empty($data['is_completed']) && $data['is_completed']) {
                $packagesData = $data['packages'];

                CreateHBLPackages::run($hbl, $packagesData);
            }

            $hbl->addStatus('HBL Preparation by driver');

            $pickup->update([
                'status' => PickupStatus::PROCESSING->value,
                'hbl_id' => $hbl->id,
            ]);

            $pickup->addStatus('Cargo collected by driver');

            DB::commit();

            return $hbl;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }
}
