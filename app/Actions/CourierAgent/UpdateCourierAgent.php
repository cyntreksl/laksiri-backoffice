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

        return $courierAgent->update($data);
    }

}
