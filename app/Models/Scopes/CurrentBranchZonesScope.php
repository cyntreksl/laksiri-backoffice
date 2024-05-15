<?php

namespace App\Models\Scopes;

use App\Actions\User\GetUserCurrentBranchID;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CurrentBranchZonesScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('branch_id', GetUserCurrentBranchID::run());
    }
}
