<?php

namespace App\Actions\Officer;

use App\Models\Officer;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateShipperOfficer
{
    use AsAction;

    public function handle(array $data): Officer
    {
        return Officer::create($data);
    }
}
