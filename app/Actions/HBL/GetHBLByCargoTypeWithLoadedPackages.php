<?php

namespace App\Actions\HBL;

use App\Models\Container;
use App\Models\HBL;
use App\Models\LoadedContainer;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLByCargoTypeWithLoadedPackages
{
    use AsAction;

    public function handle(Container $container, string $cargoType)
    {
        $loadedContainers = LoadedContainer::where('container_id', $container->id)
            ->where('is_draft', true)
            ->get(['hbl_package_id', 'hbl_id']);

        $hbl_package_ids = $loadedContainers->pluck('hbl_package_id')->toArray();
        $hbl_ids = $loadedContainers->pluck('hbl_id')->toArray();

        return HBL::where('cargo_type', $cargoType)
            ->whereIn('id', $hbl_ids)
            ->latest()
            ->with(['packages' => function ($query) use ($hbl_package_ids) {
                $query->whereIn('id', $hbl_package_ids);
            }])
            ->get();
    }
}
