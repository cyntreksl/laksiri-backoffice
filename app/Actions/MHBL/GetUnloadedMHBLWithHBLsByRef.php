<?php

namespace App\Actions\MHBL;

use App\Models\HBL;
use App\Models\MHBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUnloadedMHBLWithHBLsByRef
{
    use AsAction;

    public function handle(string $reference)
    {
        $query = MHBL::query()
            ->where('hbl_number', $reference)
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
            })
            ->with(['hbls' => function ($query) {
                $query->where('is_hold', false)
                    ->whereIn('system_status', [HBL::SYSTEM_STATUS_CASH_RECEIVED, HBL::SYSTEM_STATUS_PARTIAL_LOADED]);
            }, 'hbls.packages'])
            ->first();

        return $query;
    }
}
