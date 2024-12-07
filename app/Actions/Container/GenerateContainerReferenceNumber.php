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
        if ($last_container) {
            $parts = explode('-', $last_container->reference);
            $code = end($parts);
        }

        $next_reference = $last_container ? ((int) $code + 1) : 1; // Start from 1 if no container exists

        do {
            $reference = $branch_code.'-LD-'.str_pad($next_reference, 6, '0', STR_PAD_LEFT);
            $exists = Container::where('reference', $reference)->exists();
            $next_reference++;
        } while ($exists);

        return $reference;
    }
}
