<?php

namespace App\Actions\HBL;

use App\Actions\User\GetUserCurrentBranchID;
use App\Enum\CargoType;
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
                $package = new HBLPackage;
                $package->hbl_id = $hbl->id;
                $package->package_rule = $packageData['packageRule'] ?? $packageData['package_rule'] ?? null;
                $package->branch_id = GetUserCurrentBranchID::run();
                $package->package_type = $packageData['type'] ?? $packageData['package_type'];
                $package->length = $packageData['length'];
                $package->width = $packageData['width'] ?? 0;
                $package->height = $packageData['height'];
                $package->quantity = $packageData['quantity'];
                $package->volume = $packageData['volume'];
                $package->remarks = $packageData['remarks'];
                $package->measure_type = $packageData['measure_type'] ?? 'cm';

                // Calculate actual_weight
                $totalWeight = $packageData['totalWeight'] ?? null;
                $weight = $packageData['weight'] ?? 0;
                $actualWeight = $totalWeight ?? $weight;

                // Calculate volumetric weight: (L * W * H * Q) / 6000
                $volumetricWeight = ($packageData['length'] * $packageData['width'] * $packageData['height'] * $packageData['quantity']) / 6000;

                // Calculate chargeableWeight
                $chargeableWeight = max($actualWeight, $volumetricWeight);

                if (request()->is('v1/*')) {
                    // Logic ONLY for mobile (v1/*)

                    if ($hbl->cargo_type === CargoType::AIR_CARGO->value) {
                        $package->weight = round($chargeableWeight, 3);
                    } else {
                        $package->weight = round($actualWeight, 3);
                    }

                    $package->actual_weight = $actualWeight;
                    $package->volumetric_weight = round($volumetricWeight, 3);
                } else {
                    // Default logic (web or other APIs)

                    if ($hbl->cargo_type === CargoType::AIR_CARGO->value) {
                        $package->weight = (float) ($packageData['chargeableWeight'] ?? 0);
                    } else {
                        $package->weight = (float) ($packageData['totalWeight'] ?? $packageData['weight'] ?? 0);
                    }

                    $package->actual_weight = (float) ($packageData['totalWeight'] ?? $packageData['weight'] ?? 0);
                    $package->volumetric_weight = (float) ($packageData['volumetricWeight'] ?? 0);
                }

                $package->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
