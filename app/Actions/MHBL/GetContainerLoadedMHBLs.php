<?php

namespace App\Actions\MHBL;

use App\Models\Mhbl;
use Lorisleiva\Actions\Concerns\AsAction;

class GetContainerLoadedMHBLs
{
    use AsAction;

    public function handle(array $data)
    {
        $containerId = $data['container']; // The ID of the container to filter by

        $mhbls = MHBL::whereHas('hbls.packages.containers', function ($query) use ($containerId) {
            $query->where('container_id', $containerId);
        })
            ->with(['hbls.packages.containers'])
            ->get();

        return $mhbls;
    }
}
