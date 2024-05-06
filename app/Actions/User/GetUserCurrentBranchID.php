<?php

namespace App\Actions\User;

use App\Enum\UserStatus;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUserCurrentBranchID
{
    use AsAction;

    public function handle() :int
    {
        return session('current_branch_id');
    }
}
