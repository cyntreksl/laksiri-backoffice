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

        if (isset($data['warehouse'])) {
            $warehouse = GetBranchByName::run($data['warehouse']);
            $query->where('warehouse_id', $warehouse->id);
        }

        // Only show MHBLs where ALL HBLs are in Warehouse/Loading status
        // Exclude MHBLs that have ANY HBLs still in Cash Settlement status
        $query->with(['hbls' => function ($query) {
            $query->where('is_hold', false)
                ->whereIn('system_status', [HBL::SYSTEM_STATUS_CASH_RECEIVED, HBL::SYSTEM_STATUS_PARTIAL_LOADED]);
        }])
            ->whereHas('hbls', function ($query) {
                // Must have at least one HBL ready for loading
                $query->where('is_hold', false)
                    ->whereIn('system_status', [HBL::SYSTEM_STATUS_CASH_RECEIVED, HBL::SYSTEM_STATUS_PARTIAL_LOADED]);
            })
            ->whereDoesntHave('hbls', function ($query) {
                // Must NOT have any HBLs still in Cash Settlement
                $query->whereIn('system_status', [
                    HBL::SYSTEM_STATUS_HBL_PREPARATION_BY_DRIVER,
                    HBL::SYSTEM_STATUS_HBL_PREPARATION_BY_WAREHOUSE,
                ]);
            });

        $mhbls = $query->with('hbls.packages')->get();

        return $mhbls;
    }
}
