<?php

namespace App\Actions\CourierAgent;

use App\Models\CourierAgent;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCourierAgent
{
    use AsAction;
    public function handle($id): void
    {
        $courierAgent = CourierAgent::findOrFail($id);
        $courierAgent->delete();
    }

}
