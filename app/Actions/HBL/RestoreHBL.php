<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class RestoreHBL
{
    use AsAction;

    public function handle($id)
    {
        $hbl = HBL::withTrashed()->find($id);

        if ($hbl) {
            $hbl->restore();
        }
    }
}
