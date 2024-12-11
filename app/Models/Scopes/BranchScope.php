<?php

namespace App\Models\Scopes;

use App\Actions\User\GetUserCurrentBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $currentBranch = GetUserCurrentBranch::run();
        //        $builder->where('branch_id', '=', $currentBranch['branchId']);
        if ($currentBranch['branchType'] === 'Departure') {
            $builder->where('branch_id', '=', $currentBranch['branchId']);
        }
    }
}
