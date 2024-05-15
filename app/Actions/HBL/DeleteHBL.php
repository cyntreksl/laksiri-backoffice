<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteHBL
{
    use AsAction;

    public function handle(HBL $hbl)
    {
        $hbl->delete();
    }
}
