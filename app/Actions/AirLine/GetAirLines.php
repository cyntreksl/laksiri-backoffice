<?php

namespace App\Actions\AirLine;

use App\Models\AirLine;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAirLines
{
    use AsAction;

    public function handle(): Collection|array
    {
        return AirLine::all();
    }
}
