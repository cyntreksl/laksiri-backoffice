<?php

namespace App\Actions\VesselSchedule;

use App\Models\VesselSchedule;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAllVesselSchedules
{
    use AsAction;

    public function handle()
    {
        return VesselSchedule::with([
            'clearanceContainers.branch',
            'clearanceContainers.warehouse',
            'clearanceContainers.hbl_packages',
        ])->latest()->get();
    }
}
