<?php

namespace App\Actions\SpecialDOCharge;

use App\Models\SpecialDOCharge;
use Lorisleiva\Actions\Concerns\AsAction;

class GetSpecialDOChargeByAgent
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {
        $query = SpecialDOCharge::query();
        if (isset($data['agent_id'])) {
            $query->where('agent_id', $data['agent_id']);
        }

        if (isset($data['cargo_type'])) {
            $query->where('cargo_type', $data['cargo_type']);
        }

        if (isset($data['hbl_type'])) {
            $query->where('hbl_type', $data['hbl_type']);
        }

        return $query->get();
    }
}
