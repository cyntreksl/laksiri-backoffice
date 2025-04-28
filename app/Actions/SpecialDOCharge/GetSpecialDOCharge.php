<?php

namespace App\Actions\SpecialDOCharge;

use App\Models\SpecialDOCharge;
use Lorisleiva\Actions\Concerns\AsAction;

class GetSpecialDOCharge
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {
        return SpecialDOCharge::where('agent_id', $data['agent_id'] ?? null)
            ->where('condition', $data['condition'] ?? null)
            ->where('collected', $data['collected'] ?? null)
            ->where('package_type', $data['package_type'] ?? null)
            ->first();
    }
}
