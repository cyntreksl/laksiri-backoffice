<?php

namespace App\Observers;

use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;

class ContainerObserver
{
    /**
     * Handle the Container "created" event.
     */
    /**
     * Handle the Container "updated" event.
     */
    public function updated(Container $container): void
    {
        if ($container->wasChanged('status') && $container->status === ContainerStatus::IN_TRANSIT->value) {
            foreach ($container->hbl_packages as $package) {
                $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($package->hbl_id);
                $hbl->addStatus('Container '.ucwords(strtolower(ContainerStatus::IN_TRANSIT->value)));
            }
        }

        if ($container->wasChanged('status') && $container->status === ContainerStatus::REACHED_DESTINATION->value) {
            foreach ($container->hbl_packages as $package) {
                $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($package->hbl_id);
                $hbl->addStatus('Container '.ucwords(strtolower(ContainerStatus::REACHED_DESTINATION->value)));
            }
        }

        if ($container->wasChanged('shipment_weight')) {
            $shipmentWeight = $container->shipment_weight;

            $hblPackages = $container->hbl_packages()
                ->with('hbl')
                ->get()
                ->filter(function ($package) {
                    return $package->hbl && in_array($package->hbl->hbl_type, ['UPB', 'Gift']);
                });

            // Calculate total volume of these eligible HBL packages
            $totalVolume = $hblPackages->sum('volume');

            if ($totalVolume > 0) {
                foreach ($hblPackages as $package) {
                    // Skip if weight is set and NOT auto-updated
                    if ($package->weight > 0 && $package->auto_weight_updated === false) {
                        continue;
                    }

                    $hblWeight = ($shipmentWeight / $totalVolume) * $package->volume;
                    $package->weight = round($hblWeight, 2);
                    $package->auto_weight_updated = true;
                    $package->save();
                }
            }
        }
    }
}
