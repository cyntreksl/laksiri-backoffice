<?php

namespace App\Actions\Role\Permission;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPermissionGroup
{
    use AsAction;

    public function handle(?User $user = null): Collection
    {
        if ($user && ! $user->hasRole(['admin', 'super-admin'])) {
            return DB::table('permissions')
                ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->join('model_has_roles', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_id', $user->id)
                ->where('model_has_roles.model_type', get_class($user))
                ->select('permissions.group_name as name')
                ->groupBy('group_name')
                ->get();
        }

        return DB::table('permissions')
            ->select('group_name as name')
            ->groupBy('group_name')
            ->get();
    }
}
