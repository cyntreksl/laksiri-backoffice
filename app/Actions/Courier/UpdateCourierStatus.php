<?php

namespace App\Actions\Courier;

use App\Models\Courier;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCourierStatus
{
    use AsAction;

    public function handle(Courier $courier, string $status)
    {
        $courier->update(['status' => $status]);
    }
}
