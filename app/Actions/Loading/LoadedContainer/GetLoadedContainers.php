<?php

namespace App\Actions\Loading\LoadedContainer;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLoadedContainers
{
    use AsAction;

    public function handle()
    {
        return Container::loadedContainers()->with('loadedC')->get();
    }
}
