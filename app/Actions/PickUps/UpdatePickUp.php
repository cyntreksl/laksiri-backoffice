<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePickUp
{
    use AsAction;

    public function handle(array $data, PickUp $pickup)
    {
        $pickup->update($data);
    }
}
