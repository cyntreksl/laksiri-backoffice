<?php

namespace App\Actions\User;

use App\Enum\UserStatus;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;

class SwitchUserBranch
{
    use AsAction;

    public function handle(Branch $branch) :array
    {
        session(['current_branch_id' => $branch->id]);
        session(['current_branch_name' => $branch->name]);
        return [
            'branchName' => $branch->name,
            'branchId' => $branch->id,
        ];
    }
}
