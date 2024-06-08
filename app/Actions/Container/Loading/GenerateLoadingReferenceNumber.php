<?php

namespace App\Actions\Container\Loading;

use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateLoadingReferenceNumber
{
    use AsAction;

    public function handle(string $branch_name): string
    {
        $branch_code = '';

        if ($branch_name) {
            switch ($branch_name) {
                case 'Riyadh':
                    $branch_code = 'RY';
                    break;
                case 'Sri Lanka':
                    $branch_code = 'SL';
                    break;
                case 'Dubai':
                    $branch_code = 'DB';
                    break;
                case 'Kuwait':
                    $branch_code = 'KW';
                    break;
            }
        }

        $last_loading = Container::latest()->first();

        // Set the starting reference number
        $next_reference = $last_loading ? ((int) substr($last_loading->reference, strlen($branch_code) + 1) + 1) : 1000;

        // Pad the reference number with leading zeros
        return $branch_code.'-'.'LD-'.str_pad($next_reference, 4, '0', STR_PAD_LEFT);
    }
}
