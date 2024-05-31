<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLByCargoTypeWithPackages
{
    use AsAction;

    public function handle(string $cargoType)
    {
        return HBL::where('cargo_type', $cargoType)
            ->latest()
            ->with('packages')
            ->get();
    }
}
