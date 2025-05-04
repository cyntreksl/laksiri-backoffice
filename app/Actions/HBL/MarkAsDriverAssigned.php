<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class MarkAsDriverAssigned
{
    use AsAction;

    public function handle(HBL $hbl)
    {
        $hbl->update(['is_driver_assigned' => true]);

        return $hbl;
    }
}
