<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class SavePickUpOrder
{
    use AsAction;

    public function handle(array $item)
    {
        $pickup = PickUp::find($item['id']);
        $pickup->pickup_order = $item['pickup_order'];
        $pickup->save();
    }
}
