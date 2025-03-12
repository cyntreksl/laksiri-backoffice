<?php

namespace App\Actions\Courier;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Courier;
use App\Models\CourierPackage;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCourierPackages
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(Courier $courier, array $data)
    {
        $packages = [];

        DB::beginTransaction();

        try {
            foreach ($data as $packageData) {
                $package = new CourierPackage;
                $package->courier_id = $courier->id;
                $package->branch_id = GetUserCurrentBranchID::run();
                $package->package_type = $packageData['type'] ?? $packageData['package_type'];
                $package->length = $packageData['length'];
                $package->width = $packageData['width'];
                $package->height = $packageData['height'];
                $package->quantity = $packageData['quantity'];
                $package->volume = $packageData['volume'];
                $package->weight = $packageData['totalWeight'] ?? $packageData['weight'];
                $package->remarks = $packageData['remarks'];
                $package->measure_type = $packageData['measure_type'] ?? 'cm';
                $package->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
