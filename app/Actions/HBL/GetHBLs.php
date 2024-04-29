<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLs
{
    use AsAction;

    public function handle()
    {
        return HBL::latest()->get();
    }
}
