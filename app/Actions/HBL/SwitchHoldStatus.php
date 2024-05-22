<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class SwitchHoldStatus
{
    use AsAction;

    public function handle(HBL $hbl)
    {
        $hbl->is_hold = ! $hbl->is_hold;
        $hbl->save();
    }
}
