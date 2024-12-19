<?php

namespace App\Actions\MHBL;

use App\Actions\Branch\GetBranchByName;
use App\Models\HBL;
use App\Models\MHBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUnloadMHBLWithHBLs
{
    use AsAction;

    public function handle(array $data)
    {
        $query = MHBL::query();

        if (isset($data['cargoType'])) {
            $query->where('cargo_type', $data['cargoType']);
        }

        if (isset($data['hblType'])) {
            $query->where('hbl_type', $data['hblType']);
        }

        if (isset($data['warehouse'])) {
            $warehouse = GetBranchByName::run($data['warehouse']);
            $query->where('warehouse', $warehouse->id);
        }

        $query->with(['hbls' => function ($query) {
            $query->where('is_hold', false)
                ->whereIn('system_status', [HBL::SYSTEM_STATUS_CASH_RECEIVED, 4.1]);
        }])
            ->whereHas('hbls', function ($query) {
                $query->where('is_hold', false)
                    ->whereIn('system_status', [HBL::SYSTEM_STATUS_CASH_RECEIVED, 4.1]);
            })
            ->get();

        $mhbls = $query->with('hbls.packages')->get();

        return $mhbls;
    }
}
