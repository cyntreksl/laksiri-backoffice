<?php

namespace App\Actions\User;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUserCurrentBranch
{
    use AsAction;

    public function handle(): array
    {
        $branchName = session('current_branch_name');
        $branchId = session('current_branch_id');
        $branchType = session('current_branch_type');
        $branchCode = session('current_branch_code');
        $timezone = session('current_branch_timezone');
        $isPrimaryWarehouse = session('is_primary_warehouse');

        //      For API
        if (empty($branchId) || is_null($branchId)) {
            $primaryBranch = Branch::find(Auth::user()->primary_branch_id);
            $branchId = $primaryBranch->id;
            $branchName = $primaryBranch->name;
            $branchType = $primaryBranch->type;
            $branchCode = $primaryBranch->branch_code;
            $timezone = $primaryBranch->timezone;
            $isPrimaryWarehouse = $primaryBranch->is_primary_warehouse;
        }

        return [
            'branchName' => $branchName,
            'branchId' => $branchId,
            'branchType' => $branchType,
            'branchCode' => $branchCode,
            'timezone' => $timezone,
            'isPrimaryWarehouse' => $isPrimaryWarehouse,
        ];
    }
}
