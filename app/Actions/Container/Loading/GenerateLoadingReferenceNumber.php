<?php

namespace App\Actions\Container\Loading;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateLoadingReferenceNumber
{
    use AsAction;

    public function handle(string $branch_name): string
    {
        $branch_code = session('current_branch_code');

        $last_loading = Container::latest()->first();

        // Set the starting reference number
        $next_reference = $last_loading ? ((int) substr($last_loading->reference, strlen($branch_code) + 1) + 1) : 1000;

        // Pad the reference number with leading zeros
        return $branch_code.'-'.'LD-'.str_pad($next_reference, 4, '0', STR_PAD_LEFT);
    }
}
