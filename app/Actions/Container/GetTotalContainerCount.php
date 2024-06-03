<?php

namespace App\Actions\Container;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTotalContainerCount
{
    use AsAction;

    public function handle(): int
    {
        return Container::count();
    }
}
