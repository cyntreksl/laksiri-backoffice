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
                        }
                    }
                });
        });

        return $containersWithLoadedHBLs;
    }
}
