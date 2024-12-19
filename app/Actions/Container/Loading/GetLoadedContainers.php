<?php

namespace App\Actions\Container\Loading;

use App\Models\Container;
use App\Models\Scopes\BranchScope;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLoadedContainers
{
    use AsAction;

    public function handle()
    {
        // Retrieve containers with their loaded HBLs
        $containersWithLoadedHBLs = Container::withoutGlobalScope(BranchScope::class)
            ->with([
                'hbl_packages' => function ($query) {
                    $query->withoutGlobalScope(BranchScope::class)
                        ->with(['hbl' => function ($hblQuery) {
                            $hblQuery->with('mhbl')->withoutGlobalScope(BranchScope::class);
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
                ->groupBy('hbl.id')
                ->each(function ($hblPackage) {
                    $hbl = $hblPackage->first()->hbl;
                    $hbl->packages_count = $hblPackage->count();
                });
        });

        return $containersWithLoadedHBLs;
    }
}
