<?php

namespace App\Actions\DriverAreas;

use App\Models\Area;
use Lorisleiva\Actions\Concerns\AsAction;

class GetDriverAreas
{
    use AsAction;

    public function handle($id)
    {
        return Area::find($id);
    }
}
