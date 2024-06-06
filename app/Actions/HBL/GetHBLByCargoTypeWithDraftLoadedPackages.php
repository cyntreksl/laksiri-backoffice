<?php

namespace App\Actions\HBL;

use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLByCargoTypeWithDraftLoadedPackages
{
    use AsAction;

    public function handle(Container $container, string $cargoType)
    {
        return Hbl::where('cargo_type', $cargoType)
            ->latest()
            ->whereHas('packages.containers')->with(['packages' => function ($query) use ($container) {
                $query->whereHas('containers', function ($query) use ($container) {
                    $query->where('container_id', $container->id)
                        ->where('containers.status', ContainerStatus::DRAFT->value);
                });
            }])->get();
    }
}
