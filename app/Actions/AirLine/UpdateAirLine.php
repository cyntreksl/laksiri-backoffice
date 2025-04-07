<?php

namespace App\Actions\AirLine;

use App\Models\AirLine;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateAirLine
{
    use AsAction;

    public function handle(AirLine $airLine, array $data)
    {
        return $airLine->update($data);
    }
}
