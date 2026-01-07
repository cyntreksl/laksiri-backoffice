<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BondStoragePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the permission
        $permission = Permission::firstOrCreate([
            'name' => 'bond-storage.index',
            'guard_name' => 'web',
            'group_name' => 'Shipment Arrivals',
        ]);

        $this->command->info('✓ Created permission: bond-storage.index');

        // Optionally assign to Super Admin role
        $superAdminRole = Role::where('name', 'Super-Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permission);
            $this->command->info('✓ Assigned permission to Super-Admin role');
        }

        // You can add more roles here as needed
        // Example:
        // $warehouseManagerRole = Role::where('name', 'Warehouse Manager')->first();
        // if ($warehouseManagerRole) {
        //     $warehouseManagerRole->givePermissionTo($permission);
        //     $this->command->info('✓ Assigned permission to Warehouse Manager role');
        // }

        $this->command->info('Bond Storage permission seeding completed!');
    }
}
