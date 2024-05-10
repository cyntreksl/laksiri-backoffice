<?php

namespace App\Actions\PickUps;

use App\Models\PickUp;
use Lorisleiva\Actions\Concerns\AsAction;

class GeneratePickupReferenceNumber
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

        $last_pickup = PickUp::latest()->first();

        $next_reference = $last_pickup ? ((int) substr($last_pickup->reference, strlen($branch_code) + 4) + 1) : 1000;

        $reference = 'REF'.str_pad($next_reference, 4, '0', STR_PAD_LEFT);

        return $branch_code.'-'.$reference;
    }
}
