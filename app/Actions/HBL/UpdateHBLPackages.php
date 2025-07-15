<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLPackages
{
    use AsAction;

    public function handle(HBL $hbl, array $data)
    {
        DB::transaction(function () use ($hbl, $data) {
            $existPackages = array_filter($data, function ($item) {
                return isset($item['id']);
            });

            $newPackages = array_filter($data, function ($item) {
                return ! isset($item['id']);
            });

            $existPackageIds = collect($existPackages)->pluck('id')->toArray();

            // Delete removed packages
            $deletedPackages = $hbl->packages->reject(function ($package) use ($existPackageIds) {
                return in_array($package->id, $existPackageIds);
            });
            foreach ($deletedPackages as $packageToDelete) {
                $packageToDelete->delete();
            }

            // Update existing packages
            foreach ($existPackages as $packageData) {
                $package = $hbl->packages->where('id', $packageData['id'])->first();
                if ($package) {
                    $package->update([
                        'package_rule' => $packageData['packageRule'] ?? $packageData['package_rule'] ?? null,
                        'package_type' => $packageData['type'] ?? $packageData['package_type'],
                        'length' => $packageData['length'],
                        'width' => $packageData['width'] ?? 0,
                        'height' => $packageData['height'],
                        'quantity' => $packageData['quantity'],
                        'volume' => $packageData['volume'],
                        'remarks' => $packageData['remarks'],
                        'measure_type' => $packageData['measure_type'] ?? 'cm',
                        'weight' => $packageData['weight'] ?? 0,
                        'actual_weight' => $packageData['actual_weight'] ?? $packageData['weight'] ?? 0,
                        'volumetric_weight' => $packageData['volumetricWeight'] ?? $packageData['volumetric_weight'] ?? 0,
                    ]);
                }
            }

            // Create new packages
            CreateHBLPackages::run($hbl, $newPackages);
        });
    }
}
