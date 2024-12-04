<?php

namespace App\Actions\Zone;

use App\Models\Zone;
use Lorisleiva\Actions\Concerns\AsAction;

class GetZones
{
    use AsAction;

    public function handle()
    {
        return Zone::latest()->with(['areas'])->get();
    }
}
