<?php

namespace App\Services;

use App\Models\Container;

class ContainerWeightService
{
    public static function recalculate(Container $container): void
    {
        $shipmentWeight = $container->shipment_weight;

        if (! $shipmentWeight) {
            return;
        }

        // Get loaded HBL packages only
        $hblPackages = $container->hbl_packages()
            ->wherePivot('status', 'loaded')
            ->with('hbl')
            ->get()
            ->filter(function ($package) {
                return $package->hbl && in_array($package->hbl->hbl_type, ['UPB', 'Gift']);
            });

        $totalVolume = $hblPackages->sum('volume');

        if ($totalVolume > 0) {
            foreach ($hblPackages as $package) {
                // Skip if weight is set and NOT auto-updated
                if ($package->weight > 0 && $package->auto_weight_updated === false) {
                    continue;
                }

                $newWeight = ($shipmentWeight / $totalVolume) * $package->volume;
                $package->weight = round($newWeight, 2);
                $package->auto_weight_updated = true;
                $package->save();
            }
        }
    }
}
