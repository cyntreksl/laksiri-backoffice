<?php

namespace App\Actions\User;

use Lorisleiva\Actions\Concerns\AsAction;

class GetUserCurrentBranchID
{
    use AsAction;

    public function handle() :int
    {
        return session('current_branch_id');
    }
}
