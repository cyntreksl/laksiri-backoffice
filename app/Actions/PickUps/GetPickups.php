<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPickups
{
    use AsAction;

    public function handle()
    {
        return  PickUp::latest()->get();
    }
}
