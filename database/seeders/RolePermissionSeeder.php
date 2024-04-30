<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        DB::table('roles')->delete();

        // Create roles
        Role::updateOrCreate(['name' => 'admin']);
        Role::updateOrCreate(['name' => 'empty']);
        Role::updateOrCreate(['name' => 'viewer']);

        $this->command->info('Default Roles added.');
    }
}
