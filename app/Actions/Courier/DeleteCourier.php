<?php

namespace App\Actions\Courier;

use App\Models\Courier;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCourier
{
    use AsAction;

    public function handle(Courier $courier)
    {
        $courier->delete();
    }
}
