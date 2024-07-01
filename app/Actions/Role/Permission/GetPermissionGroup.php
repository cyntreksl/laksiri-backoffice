<?php

namespace App\Actions\Role\Permission;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPermissionGroup
{
    use AsAction;

    public function handle(): Collection
    {
        return DB::table('permissions')
            ->select('group_name as name')
            ->groupBy('group_name')
            ->get();
    }
}
