<?php

namespace App\Actions\PickupType;

use App\Models\PickupType;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePickupType
{
    use AsAction;

    public function handle(array $data): PickupType
    {
        return PickupType::create($data);
    }
}
