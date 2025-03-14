<?php

namespace App\Actions\Courier;

use App\Models\Courier;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCourierByCourierNumber
{
    use AsAction;

    public function handle(string $courier_number): Courier
    {
        return Courier::where('courier_number', $courier_number)->firstOrFail();
    }
}
