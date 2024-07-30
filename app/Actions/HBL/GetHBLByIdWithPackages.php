<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLByIdWithPackages
{
    use AsAction;

    public function handle($hbl)
    {
        return HBL::where('id', $hbl)
            ->withTrashed()
            ->withCount('packages')
            ->with('packages')
            ->withSum('packages', 'weight')
            ->withSum('packages', 'volume')
            ->first();
    }
}
