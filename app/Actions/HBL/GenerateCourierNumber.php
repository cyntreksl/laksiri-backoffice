<?php

namespace App\Actions\HBL;

use App\Models\Courier;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateCourierNumber
{
    use AsAction;

    public function handle($currentBranchId)
    {
        $last_courier = Courier::where('branch_id', $currentBranchId)->latest()->first();
        $next_number = $last_courier ? ((int) substr($last_courier->courier_number, 3) + 1) : 1;
        do {
            $courier_number = strtoupper(str_pad($currentBranchId, 3, '0', STR_PAD_LEFT)).str_pad($next_number, 6, '0', STR_PAD_LEFT);
            $exists = Courier::withoutGlobalScopes()->where('courier_number', $courier_number)->exists();
            $next_number++;
        } while ($exists);

        return $courier_number;

    }
}
