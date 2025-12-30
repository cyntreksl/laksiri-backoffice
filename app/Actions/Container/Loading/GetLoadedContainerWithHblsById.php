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
            $container->hbls = $container
                ->hbl_packages
                ->pluck('hbl')
                ->unique();
            $container->hbl_count = $container->hbls->count();

            $container
                ->hbl_packages
                ->groupBy(fn ($package) => $package->hbl?->id)
                ->each(function ($hblPackage, $hblId) {
                    if ($hblId !== null) {
                        $hbl = $hblPackage->first()->hbl;
                        if ($hbl) {
                            $hbl->packages_count = $hblPackage->count();
                        }
                    }
                });
        });

        return $containersWithLoadedHBLs;
    }
}
