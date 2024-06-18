<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLsWithPackages
{
    use AsAction;

    public function handle()
    {
        return HBL::withTrashed()->latest()
            ->withCount('packages')
            ->with('packages')
            ->withSum('packages', 'weight')
            ->withSum('packages', 'volume')
            ->get();
    }
}
