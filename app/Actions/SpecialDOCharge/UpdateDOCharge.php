<?php

namespace App\Actions\SpecialDOCharge;

use App\Models\SpecialDOCharge;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateDOCharge
{
    use AsAction;

    public function handle(array $data, SpecialDOCharge $specialDOCharge)
    {
        return $specialDOCharge->update([
            'agent_id' => $data['agent_id'],
            'cargo_type' => $data['cargo_type'],
            'hbl_type' => $data['hbl_type'],
            'collected' => $data['collected'],
            'condition' => $data['condition'],
            'package_type' => $data['package_type'],
            'charge' => $data['charge'],
        ]);
    }
}
