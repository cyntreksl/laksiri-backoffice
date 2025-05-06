<?php

namespace App\Actions\Container;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class GetContainerPayment
{
    use AsAction;

    public function handle(Container $container)
    {
        return $container->payment ?? [];
    }
}
