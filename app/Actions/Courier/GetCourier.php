<?php

namespace App\Actions\Courier;

use App\Models\Courier;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCourier
{
    use AsAction;

    public function handle(string $id): Courier
    {
        return Courier::findOrFail($id);
    }
}
