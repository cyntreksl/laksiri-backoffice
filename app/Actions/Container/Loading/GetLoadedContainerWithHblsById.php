<?php

namespace App\Actions\Container\Loading;

use App\Models\Container;
use App\Models\Scopes\BranchScope;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLoadedContainerWithHblsById
{
    use AsAction;

    public function handle(string $id)
    {
        // Retrieve containers with their loaded HBLs
        $containersWithLoadedHBLs = Container::withoutGlobalScope(BranchScope::class)
            ->where('id', $id)
            ->with([
                'branch',
                'warehouse',
                'latestDetainRecord',
                'arrivedPrimaryWarehouseByUser:id,name',
                'hbl_packages' => function ($query) {
                    $query->withoutGlobalScope(BranchScope::class)
                        ->with(['hbl' => function ($hblQuery) {
                            $hblQuery->with('mhbl', 'mhbl.shipper', 'mhbl.consignee')->withoutGlobalScope(BranchScope::class);
                        }]);
                },
                // Load all historical packages from duplicate_hbl_packages (both loaded and unloaded)
                'duplicate_hbl_packages' => function ($query) {
                    $query->withoutGlobalScope(BranchScope::class)
                        ->with(['hbl' => function ($hblQuery) {
                            $hblQuery->with('mhbl', 'mhbl.shipper', 'mhbl.consignee')->withoutGlobalScope(BranchScope::class);
                        }])
                        ->withPivot('status', 'loaded_by', 'unloaded_by');
                },
            ])
            ->withCount(['hbl_packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class);
            }])
            ->withSum(['hbl_packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class);
            }], 'weight')
            ->withSum(['hbl_packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class);
            }], 'volume')
            ->get();

        $containersWithLoadedHBLs->each(function ($container) {
            // Get currently loaded package IDs
            $currentlyLoadedPackageIds = $container->hbl_packages->pluck('id')->toArray();
            
            // Use duplicate_hbl_packages as the source of truth for all packages
            $container->hbls = $container
                ->duplicate_hbl_packages
                ->map(function ($package) use ($currentlyLoadedPackageIds) {
                    $hbl = $package->hbl;
                    if ($hbl) {
                        // Mark if this package is unloaded
                        $hbl->is_unloaded = $package->pivot->status === 'unloaded';
                        $hbl->package_status = $package->pivot->status;
                    }
                    return $hbl;
                })
                ->filter()
                ->unique('id')
                ->values();
            
            $container->hbl_count = $container->hbls->count();

            // Group packages by HBL and count them, including unloaded status
            $container
                ->duplicate_hbl_packages
                ->groupBy(fn ($package) => $package->hbl?->id)
                ->each(function ($hblPackages, $hblId) use ($currentlyLoadedPackageIds) {
                    if ($hblId !== null) {
                        $hbl = $hblPackages->first()->hbl;
                        if ($hbl) {
                            $hbl->packages_count = $hblPackages->count();
                            // Check if any package for this HBL is unloaded
                            $hbl->has_unloaded_packages = $hblPackages->some(fn($pkg) => $pkg->pivot->status === 'unloaded');
                            $hbl->is_fully_unloaded = $hblPackages->every(fn($pkg) => $pkg->pivot->status === 'unloaded');
                            
                            // If this HBL has an MHBL, propagate the unloaded status to the MHBL
                            if ($hbl->mhbl) {
                                $hbl->mhbl->has_unloaded_packages = $hbl->has_unloaded_packages;
                                $hbl->mhbl->is_fully_unloaded = $hbl->is_fully_unloaded;
                            }
                        }
                    }
                });
            
            // Calculate MHBL-level unloaded status by checking all HBLs under each MHBL
            $mhblStatuses = [];
            foreach ($container->hbls as $hbl) {
                if ($hbl->mhbl) {
                    $mhblId = $hbl->mhbl->id;
                    if (!isset($mhblStatuses[$mhblId])) {
                        $mhblStatuses[$mhblId] = [
                            'has_any_unloaded' => false,
                            'all_unloaded' => true,
                        ];
                    }
                    
                    if ($hbl->has_unloaded_packages || $hbl->is_fully_unloaded) {
                        $mhblStatuses[$mhblId]['has_any_unloaded'] = true;
                    }
                    
                    if (!$hbl->is_fully_unloaded) {
                        $mhblStatuses[$mhblId]['all_unloaded'] = false;
                    }
                }
            }
            
            // Apply MHBL statuses
            foreach ($container->hbls as $hbl) {
                if ($hbl->mhbl && isset($mhblStatuses[$hbl->mhbl->id])) {
                    $hbl->mhbl->has_unloaded_packages = $mhblStatuses[$hbl->mhbl->id]['has_any_unloaded'];
                    $hbl->mhbl->is_fully_unloaded = $mhblStatuses[$hbl->mhbl->id]['all_unloaded'];
                }
            }
        });

        return $containersWithLoadedHBLs;
    }
}
