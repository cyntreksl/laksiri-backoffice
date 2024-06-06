<?php

namespace App\Actions\Container\Loading;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLoadedContainers
{
    use AsAction;

    public function handle()
    {
        return Container::loadedContainers()->with('hbl_packages')->get();

    }
}
