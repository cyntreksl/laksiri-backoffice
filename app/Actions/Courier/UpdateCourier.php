<?php

namespace App\Actions\Courier;

use App\Models\Courier;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCourier
{
    use AsAction;

    public function handle(Courier $courier, array $data)
    {
        $courier->update($data);

        return $courier;
    }
}
