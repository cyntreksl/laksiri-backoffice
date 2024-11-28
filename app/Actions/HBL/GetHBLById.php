<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLById
{
    use AsAction;

    public function handle($hbl): HBL
    {
        return HBL::withoutGlobalScopes()->where('id', $hbl)->first();
    }
}
