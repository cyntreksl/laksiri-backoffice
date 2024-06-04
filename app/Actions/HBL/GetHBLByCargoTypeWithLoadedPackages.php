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
        $hbl_package_ids = LoadedContainer::where('container_id', $container->id)
            ->where('is_draft', true)
            ->pluck('hbl_package_id')
            ->toArray();

        $hbl_ids = LoadedContainer::where('container_id', $container->id)
            ->where('is_draft', true)
            ->pluck('hbl_id')
            ->toArray();

        return HBL::where('cargo_type', $cargoType)
            ->whereIn('id', $hbl_ids)
            ->latest()
            ->with(['packages' => function ($query) use ($hbl_package_ids) {
                $query->whereIn('id', $hbl_package_ids);
            }])
            ->get();
    }
}
