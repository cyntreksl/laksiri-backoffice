<?php

namespace App\Actions\MHBL;

use App\Models\Branch;
use App\Models\MHBL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateMHBLReferenceNumber
{
    use AsAction;

    public function handle(): string
    {
        return DB::transaction(function () {
            $branch_code = session('current_branch_code')
                ?? Branch::where('id', Auth::user()->primary_branch_id)->pluck('branch_code')->first();

            // Lock the table for update to prevent duplicates
            $last_mhbl = MHBL::whereNotNull('reference')
                ->lockForUpdate()
                ->latest()
                ->first();

            $next_number = 1;

            if ($last_mhbl) {
                $last_number = (int) substr($last_mhbl->reference, strpos($last_mhbl->reference, 'REF') + 3);
                $next_number = $last_number + 1;
            }

            $reference = $branch_code.'-REF'.str_pad($next_number, 6, '0', STR_PAD_LEFT);

            return $reference;
        });
    }
}
