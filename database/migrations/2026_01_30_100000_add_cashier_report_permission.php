<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create permission for cashier daily collection report
        $permission = Permission::firstOrCreate([
            'name' => 'cashier.view daily collection report',
            'guard_name' => 'web',
        ], [
            'group_name' => 'Call Center',
        ]);

        // Assign permission to relevant roles
        $roles = ['super admin', 'call center'];

        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('name', 'cashier.view daily collection report')->delete();
    }
};
