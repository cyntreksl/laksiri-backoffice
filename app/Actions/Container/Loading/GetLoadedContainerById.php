<?php

namespace App\Actions\Container\Loading;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLoadedContainerById
{
    use AsAction;

    public function handle(Container $container)
    {
        // Retrieve containers with their loaded HBLs
        $container->with(['hbl_packages' => function ($query) {
            $query->wherePivot('status', 'loaded');
        }])->get();

        $container->hbls = $container
            ->hbl_packages
            ->pluck('hbl')
            ->unique();

        return $container;
    }
}
