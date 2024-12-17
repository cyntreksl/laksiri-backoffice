<?php

namespace App\Actions\MHBL;

use App\Models\Mhbl;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteMHBL
{
    use AsAction;

    public function handle(MHBL $mhbl)
    {
        $mhbl->delete();
    }
}
