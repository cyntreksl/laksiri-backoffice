<?php

namespace App\Actions\PickUps;

use App\Enum\PickupStatus;
use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPickupsByDriver
{
    use AsAction;

    public function handle(array $data)
    {
        $query = PickUp::query()->assignedToDriver();

        if (isset($data['start_date']) && isset($data['end_date'])) {
            $query->whereBetween('pickup_date', [$data['start_date'], $data['end_date']]);
        }

        return $query
            ->where('status', PickupStatus::PENDING)
            ->orderBy('pickup_order')
            ->get();
    }
}
