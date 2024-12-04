<?php

namespace App\Actions\Container;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateContainerReferenceNumber
{
    use AsAction;

    public function handle(): string
    {
        $branch_code = session('current_branch_code');
        $last_container = Container::latest()->first();

        $next_reference = $last_container ? ((int) substr($last_container->reference, 7) + 1) : 000001;

        $reference = $branch_code.'-'.'LD'.'-'.str_pad($next_reference, 6, '0', STR_PAD_LEFT);

        return $reference;
    }
}
