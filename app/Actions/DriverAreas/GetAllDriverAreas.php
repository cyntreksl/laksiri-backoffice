<?php

namespace App\Actions\DriverAreas;

use App\Models\Area;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAllDriverAreas
{
    use AsAction;

    public function handle()
    {
        return Area::latest()->get();
    }
}
