<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use App\Models\HBLDeliver;
use Lorisleiva\Actions\Concerns\AsAction;

class UnassignDriver
{
    use AsAction;

    public function handle(HBL $hbl): void
    {
        $hbl->update(['is_driver_assigned' => false]);

        HBLDeliver::where('hbl_id', $hbl->id)->delete();
    }
}
