<?php

namespace App\Actions\BondStorage;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class GetShipmentPackages
{
    use AsAction;

    public function handle(int $containerId): array
    {
        $container = Container::with([
            'hbl_packages' => function ($query) {
                // Get packages that are in draft-unload status (unloaded to warehouse)
                $query->wherePivot('status', 'draft-unload')
                    ->where(function ($q) {
                        $q->whereNull('bond_storage_number')
                          ->orWhere('bond_storage_number', '');
                    });
            },
            'hbl_packages.hbl.mhbl',
        ])->findOrFail($containerId);

        // If no packages with draft-unload, try to get all unloaded packages
        if ($container->hbl_packages->isEmpty()) {
            $container = Container::with([
                'hbl_packages' => function ($query) {
                    $query->where('is_unloaded', true)
                        ->where(function ($q) {
                            $q->whereNull('bond_storage_number')
                              ->orWhere('bond_storage_number', '');
                        });
                },
                'hbl_packages.hbl.mhbl',
            ])->findOrFail($containerId);
        }

        // Group packages by HBL
        $groupedPackages = $container->hbl_packages->groupBy('hbl_id')->map(function ($packages, $hblId) {
            $firstPackage = $packages->first();
            $hbl = $firstPackage->hbl;

            return [
                'hbl_id' => $hblId,
                'hbl_number' => $hbl->hbl_number,
                'mhbl_reference' => $hbl->mhbl ? $hbl->mhbl->reference : null,
                'is_overland' => $hbl->is_overland ?? false,
                'is_unmanifest' => $hbl->is_unmanifest ?? false,
                'packages' => $packages->map(function ($package) {
                    return [
                        'id' => $package->id,
                        'hbl_id' => $package->hbl_id,
                        'package_type' => $package->package_type,
                        'quantity' => $package->quantity,
                        'weight' => $package->weight,
                        'volume' => $package->volume,
                        'created_at' => $package->created_at,
                        'bond_storage_number' => $package->bond_storage_number,
                    ];
                })->values()->all(),
            ];
        })->sortBy('hbl_number')->values()->all();

        return [
            'container' => [
                'id' => $container->id,
                'reference' => $container->reference,
                'cargo_type' => $container->cargo_type,
                'container_type' => $container->container_type,
            ],
            'hbl_groups' => $groupedPackages,
            'total_packages' => $container->hbl_packages->count(),
        ];
    }
}
