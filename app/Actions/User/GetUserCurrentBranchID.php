<?php

namespace App\Actions\User;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUserCurrentBranchID
{
    use AsAction;

    public function handle() :int
    {
        $branchId = session('current_branch_id');

        if (empty($branchId)) {
            $primaryBranch = Branch::find(Auth::user()->primary_branch_id);
            $branchId = $primaryBranch->id;
        }

        return $branchId;
    }
}
