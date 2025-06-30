<?php

namespace App\Actions\User;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class SwitchUserBranch
{
    use AsAction;

    public function handle(Branch $branch): array
    {
        session(['current_branch_id' => $branch->id]);
        session(['current_branch_name' => $branch->name]);
        session(['current_branch_type' => $branch->type]);
        session(['current_branch_code' => $branch->branch_code]);
        session(['current_branch_timezone' => $branch->timezone]);
        session(['is_primary_warehouse' => $branch->is_primary_warehouse]);

        $user = Auth::user();
        if ($user->last_logged_branch_id !== $branch->id) {
            $user->update(['last_logged_branch_id' => $branch->id]);
        }

        return [
            'branchName' => $branch->name,
            'branchId' => $branch->id,
            'isPrimaryWarehouse' => $branch->is_primary_warehouse,
        ];
    }
}
