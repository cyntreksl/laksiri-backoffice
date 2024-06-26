<?php

namespace App\Actions\Role\Permission;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPermissionsByGroupName
{
    use AsAction;

    public function handle(string $group_name): Collection
    {
        return DB::table('permissions')
            ->select('name', 'id')
            ->where('group_name', $group_name)
            ->get();
    }
}
