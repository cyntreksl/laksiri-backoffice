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
                $query->whereNull('bond_storage_number')
                    ->orWhere('bond_storage_number', '');
            },
            'hbl_packages.hbl.mhbl',
        ])->findOrFail($containerId);

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
        ];
    }
}
