<?php

namespace App\Actions\Container;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateContainerSystemStatus
{
    use AsAction;

    public function handle(Container $container, float $status): Container
    {
        $container->system_status = $status;
        $container->save();

        return $container;
    }
}
