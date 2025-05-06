<?php

namespace App\Actions\ContainerPayment;

use App\Models\ContainerPayment;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteContainerPayment
{
    use AsAction;

    public function handle(ContainerPayment $containerPayment)
    {
        $containerPayment->delete();
    }
}
