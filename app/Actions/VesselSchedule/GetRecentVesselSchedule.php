<?php

namespace App\Actions\VesselSchedule;

use App\Models\VesselSchedule;
use Lorisleiva\Actions\Concerns\AsAction;

class GetRecentVesselSchedule
{
    use AsAction;

    public function handle()
    {
        return VesselSchedule::latest()->first();
    }
}
