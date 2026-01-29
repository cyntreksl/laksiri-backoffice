<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the switch-branch permission
        $permission = Permission::firstOrCreate([
            'name' => 'users.switch-branch',
            'guard_name' => 'web',
        ], [
            'group_name' => 'User',
        ]);

        // Assign to super-admin and admin roles by default
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permission);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the permission
        $permission = Permission::where('name', 'users.switch-branch')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
