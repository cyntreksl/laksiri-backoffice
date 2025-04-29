<?php

namespace App\Actions\SpecialDOCharge;

use App\Models\SpecialDOCharge;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetSpecialDOCharges
{
    use AsAction;

    public function handle(): Collection|array
    {
        return SpecialDOCharge::join('branches', 'special_do_charges.agent_id', '=', 'branches.id')->select('special_do_charges.*', 'branches.name as agent')
            ->get();
    }
}
