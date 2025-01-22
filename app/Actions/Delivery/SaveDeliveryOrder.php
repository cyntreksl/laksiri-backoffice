<?php

namespace App\Actions\Delivery;

use App\Models\HBLDeliver;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveDeliveryOrder
{
    use AsAction;

    public function handle(array $delivery)
    {
        $pickup = HBLDeliver::find($delivery['id']);
        $pickup->deliver_order = $delivery['deliver_order'];
        $pickup->save();
    }
}
