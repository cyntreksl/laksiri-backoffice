<?php

namespace App\Actions\Container\Loading;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLoadedContainers
{
    use AsAction;

    public function handle()
    {
        // Retrieve containers with their loaded HBLs
        $containersWithLoadedHBLs = Container::with(['hbl_packages' => function ($query) {
            $query->wherePivot('status', 'loaded');
        }])
            ->withCount('hbl_packages')
            ->withSum('hbl_packages', 'weight')
            ->withSum('hbl_packages', 'volume')
            ->get();

        // Filter out packages without HBL
        $containersWithLoadedHBLs->each(function ($container) {
            $container->hbls = $container
                ->hbl_packages
                ->pluck('hbl')
                ->unique();
            $container->hbl_count = $container->hbls->count();
        });

        return $containersWithLoadedHBLs;
    }
}
