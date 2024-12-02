<?php

namespace App\Actions\HBL;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\HBL;
use App\Models\HBLPackage;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateHBLPackages
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(HBL $hbl, array $data)
    {
        $packages = [];

        DB::beginTransaction();

        try {
            foreach ($data as $packageData) {
                $package = new HBLPackage();
                $package->hbl_id = $hbl->id;
                $package->package_rule = $packageData['packageRule'] ?? $packageData['package_rule'] ?? null;
                $package->branch_id = GetUserCurrentBranchID::run();
                $package->package_type = $packageData['type'] ?? $packageData['package_type'];
                $package->length = $packageData['length'];
                $package->width = $packageData['width'];
                $package->height = $packageData['height'];
                $package->quantity = $packageData['quantity'];
                $package->volume = $packageData['volume'];
                $package->weight = $packageData['totalWeight'] ?? $packageData['weight'];
                $package->remarks = $packageData['remarks'];
                $package->measure_type = $packageData['measure_type'];
                $package->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
