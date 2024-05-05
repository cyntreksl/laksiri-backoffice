<?php

namespace App\Actions\User;

use App\Enum\UserStatus;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUserCurrentBranch
{
    use AsAction;

    public function handle() :array
    {
        $branchName = session('current_branch_name');
        $branchId =session('current_branch_id');

        return [
          'branchName' => $branchName,
          'branchId' => $branchId,
        ];
    }
}
