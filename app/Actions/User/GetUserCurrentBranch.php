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

        //      For API
        if (empty($branchId)) {
            $primaryBranch = Branch::find(Auth::user()->primary_branch_id);
            $branchId = $primaryBranch->id;
            $branchName = $primaryBranch->name;
            $branchType = $primaryBranch->type;
        }

        return [
            'branchName' => $branchName,
            'branchId' => $branchId,
            'branchType' => $branchType,
        ];
    }
}
