<?php

namespace App\Actions\AirLine;

use App\Models\AirLine;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAirLineById
{
    use AsAction;

    public function handle(int $id): AirLine
    {
        return AirLine::findOrFail($id);
    }
}
