<?php

namespace App\Actions\Loading\LoadedContainer;

use App\Models\LoadedContainer;
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

        $last_loading = LoadedContainer::latest()->first();

        $next_reference = $last_loading ? ((int) substr($last_loading->reference, strlen($branch_code) + 4) + 1) : 1000;

        $reference = 'LD'.str_pad($next_reference, 4, '0', STR_PAD_LEFT);

        return $branch_code.'-'.$reference;
    }
}
