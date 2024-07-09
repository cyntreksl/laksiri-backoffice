<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLByCargoTypeWithUnloadedPackages
{
    use AsAction;

    public function handle(array $data)
    {
        $query = HBL::query();

        if (isset($data['cargoType'])) {
            $query->where('cargo_type', $data['cargoType']);
        }

        if (isset($data['hblType'])) {
            $query->where('hbl_type', $data['hblType']);
        }

        if (isset($data['warehouse'])) {
            $query->where('warehouse', $data['warehouse']);
        }

        return $query->whereIn('system_status', [4, 4.1])
            ->latest()
            ->with(['packages' => function ($query) {
                $query->where('is_loaded', false);
            }])
            ->get();
    }
}
