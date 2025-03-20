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
                $query->where('is_hold', false)
                    ->whereIn('system_status', [HBL::SYSTEM_STATUS_CASH_RECEIVED, 4.1]);
            })
            ->whereDoesntHave('hbls', function ($query) {
                $query->where('is_hold', true)
                    ->orWhereNotIn('system_status', [HBL::SYSTEM_STATUS_CASH_RECEIVED, 4.1]);
            })
            ->with(['hbls' => function ($query) {
                $query->where('is_hold', false)
                    ->whereIn('system_status', [HBL::SYSTEM_STATUS_CASH_RECEIVED, 4.1]);
            }, 'hbls.packages'])
            ->first();

        return $query;
    }
}
