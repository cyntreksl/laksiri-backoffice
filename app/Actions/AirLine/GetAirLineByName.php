<?php

namespace App\Actions\AirLine;

use App\Models\AirLine;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAirLineByName
{
    use AsAction;

    public function handle(string $airLineName): ?AirLine
    {
        return AirLine::where('name', $airLineName)->first();
    }
}
