<?php

namespace App\Actions\PickUps;

use App\Actions\Branch\GetBranchByName;
use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class GeneratePickupReferenceNumber
{
    use AsAction;

    public function handle(string $branch_name): string
    {
        $branch = GetBranchByName::run($branch_name);
        $branch_code = session('current_branch_code') ?? $branch['branch_code'];

        $last_pickup = PickUp::latest()->first();

        $next_reference = $last_pickup ? ((int) substr($last_pickup->reference, strlen($branch_code) + 6) + 1) : 000001;

        $reference = 'REF'.str_pad($next_reference, 6, '0', STR_PAD_LEFT);

        return $branch_code.'-'.$reference;
    }
}
