<?php

namespace App\Actions\AirLine;

use App\Models\AirLine;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteAirLine
{
    use AsAction;

    public function handle(AirLine $airLine)
    {
        $airLine->delete();
    }
}
