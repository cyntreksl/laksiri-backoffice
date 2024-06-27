<?php

namespace App\Actions\Container;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateContainerReferenceNumber
{
    use AsAction;

    public function handle(): string
    {

        $last_container = Container::latest()->first();

        $next_reference = $last_container ? ((int) substr($last_container->reference, 6) + 1) : 000001;

        $reference = 'LD'.str_pad($next_reference, 6, '0', STR_PAD_LEFT);

        return $reference;
    }
}
