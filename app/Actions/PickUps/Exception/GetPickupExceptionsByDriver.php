<?php

namespace App\Actions\PickUps\Exception;

use App\Models\PickupException;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPickupExceptionsByDriver
{
    use AsAction;

    public function handle(array $data)
    {
        $query = PickupException::query()->assignedToDriver();

        if (isset($data['start_date']) && isset($data['end_date'])) {
            $query->whereBetween('pickup_date', [$data['start_date'], $data['end_date']]);
        }

        return $query->get();
    }
}
