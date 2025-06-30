<?php

namespace App\Actions\VesselSchedule;

use App\Models\VesselSchedule;
use Lorisleiva\Actions\Concerns\AsAction;

class GetVesselSchedule
{
    use AsAction;

    public function handle($vesselScheduleID)
    {
        return VesselSchedule::find($vesselScheduleID);
    }
}
