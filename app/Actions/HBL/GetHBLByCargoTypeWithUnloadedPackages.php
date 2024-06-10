<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLByCargoTypeWithUnloadedPackages
{
    use AsAction;

    public function handle(string $cargoType)
    {
        return HBL::where('cargo_type', $cargoType)
            ->whereIn('system_status', [4, 4.1])
            ->latest()
            ->with(['packages' => function ($query) {
                $query->where('is_loaded', false);
            }])
            ->get();
    }
}
