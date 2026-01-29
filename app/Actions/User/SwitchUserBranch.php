<?php

namespace App\Actions\User;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class SwitchUserBranch
{
    use AsAction;

    public function handle(Branch $branch, bool $skipValidation = false): array
    {
        $user = Auth::user();
        $oldBranchId = session('current_branch_id');
        $oldBranchName = session('current_branch_name');

        // Only validate branch access if not skipping validation (e.g., during manual switch)
        if (!$skipValidation && !$user->branches->contains($branch->id)) {
            Log::warning('Unauthorized branch switch attempt', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'attempted_branch_id' => $branch->id,
                'attempted_branch_name' => $branch->name,
            ]);

            throw new \Exception('You do not have access to this branch.');
        }

        session(['current_branch_id' => $branch->id]);
        session(['current_branch_name' => $branch->name]);
        session(['current_branch_type' => $branch->type]);
        session(['current_branch_code' => $branch->branch_code]);
        session(['current_branch_timezone' => $branch->timezone]);
        session(['is_primary_warehouse' => $branch->is_primary_warehouse]);

        if ($user->last_logged_branch_id !== $branch->id) {
            $user->update(['last_logged_branch_id' => $branch->id]);
        }

        // Audit log for branch switch
        Log::info('User switched branch', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'old_branch_id' => $oldBranchId,
            'old_branch_name' => $oldBranchName,
            'new_branch_id' => $branch->id,
            'new_branch_name' => $branch->name,
            'timestamp' => now()->toDateTimeString(),
            'skip_validation' => $skipValidation,
        ]);

        return [
            'branchName' => $branch->name,
            'branchId' => $branch->id,
            'isPrimaryWarehouse' => $branch->is_primary_warehouse,
        ];
    }
}
