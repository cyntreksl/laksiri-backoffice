<?php

namespace App\Actions\CourierAgent;

use App\Models\CourierAgent;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCourierAgent
{
    use AsAction;

    public function handle(array $data, $id)
    {
        $courierAgent = CourierAgent::find($id);

        if ($courierAgent) {
            $courierAgent->update($data);

            return $courierAgent;
        }

        throw new \Exception('Courier Agent not found.');
    }
}
