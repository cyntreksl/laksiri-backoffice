<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateHBLReferenceNumber
{
    use AsAction;

    public function handle(): string
    {
        $branch_code = session('current_branch_code');
        $last_hbl = HBL::whereNotNull('reference')->latest()->first();

        $next_reference = $last_hbl ? ((int) substr($last_hbl->reference, 6) + 1) : 000001;

        $reference = $branch_code.'-'.'REF'.str_pad($next_reference, 6, '0', STR_PAD_LEFT);

        return $reference;
    }
}
