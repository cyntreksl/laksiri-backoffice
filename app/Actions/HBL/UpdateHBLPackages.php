<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use App\Models\HBLPackage;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLPackages
{
    use AsAction;

    public function handle(HBL $hbl, array $data)
    {
        DB::transaction(function () use ($hbl, $data) {
            $newPackages = [];

            foreach ($data as $packageData) {
                if (isset($packageData['id'])) {
                    $package = HBLPackage::find($packageData['id']);
                    $package->update([
                        'package_type' => $packageData['package_type'],
                        'package_rule' => $packageData['package_rule'],
                        'length' => $packageData['length'],
                        'width' => $packageData['width'],
                        'height' => $packageData['height'],
                        'quantity' => $packageData['quantity'],
                        'volume' => $packageData['volume'],
                        'weight' => $packageData['weight'],
                        'remarks' => $packageData['remarks'],
                    ]);

                } else {
                    $newPackages[] = $packageData;
                }
            }
            CreateHBLPackages::run($hbl, $newPackages);
        });
    }
}
