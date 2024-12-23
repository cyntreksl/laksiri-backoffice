<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        Role::updateOrCreate(['name' => 'admin']);
        Role::updateOrCreate(['name' => 'empty']);
        Role::updateOrCreate(['name' => 'viewer']);
        Role::updateOrCreate(['name' => 'driver']);
        Role::updateOrCreate(['name' => 'customer']);
        Role::updateOrCreate(['name' => 'call center']);
        Role::updateOrCreate(['name' => 'boned area']);

        $this->command->info('Default Roles added.');

        $this->assignPermissions();
    }

    protected function assignPermissions(): void
    {
        $role = Role::where('name', 'admin')->first();
        for ($i = 0; $i < count(self::defaultPermissions()); $i++) {
            $permissionGroup = self::defaultPermissions()[$i]['group_name'];
            for ($j = 0; $j < count(self::defaultPermissions()[$i]['permissions']); $j++) {
                $permission = Permission::updateOrCreate([
                    'name' => self::defaultPermissions()[$i]['permissions'][$j],
                    'group_name' => $permissionGroup,
                ]);
                $role->givePermissionTo($permission);
            }
        }
    }

    public static function defaultPermissions(): array
    {
        return [
            [
                'group_name' => 'User',
                'permissions' => [
                    'users.create',
                    'users.list',
                    'users.edit',
                    'users.delete',
                ],
            ],

            [
                'group_name' => 'Role',
                'permissions' => [
                    'roles.create',
                    'roles.list',
                    'roles.edit',
                    'roles.delete',
                ],
            ],

            [
                'group_name' => 'Pickup',
                'permissions' => [
                    'pickups.pending pickups',
                    'pickups.create',
                    'pickups.show',
                    'pickups.edit',
                    'pickups.delete',
                    'pickups.assign driver',
                    'pickups.show pickup order',
                    'pickups.update pickup order',
                    'pickups.show pickup exceptions',
                    'pickups.retry',
                ],
            ],

            [
                'group_name' => 'HBL',
                'permissions' => [
                    'hbls.index',
                    'hbls.create',
                    'hbls.show',
                    'hbls.edit',
                    'hbls.delete',
                    'hbls.hold and release',
                    'hbls.show cancelled hbls',
                    'hbls.restore',
                    'hbls.download pdf',
                    'hbls.download invoice',
                    'hbls.download barcode',
                    'hbls.upload documents',
                    'hbls.delete documents',
                    'hbls.issue token',
                ],
            ],

            [
                'group_name' => 'Cash Settlement',
                'permissions' => [
                    'cash.index',
                    'cash.show',
                    'cash.hold and release',
                    'cash.update payment',
                    'cash.cash received',
                ],
            ],

            [
                'group_name' => 'Warehouse',
                'permissions' => [
                    'warehouse.index',
                    'warehouse.show',
                    'warehouse.assign zone',
                    'warehouse.hold and release',
                    'warehouse.download barcode',
                    'warehouse.revert to cash settlement',
                ],
            ],

            [
                'group_name' => 'Container',
                'permissions' => [
                    'container.index',
                    'container.create',
                    'container.load to container',
                    'container.upload documents',
                    'container.delete documents',
                    'container.edit',
                    'container.download documents',
                ],
            ],

            [
                'group_name' => 'Loaded Shipment',
                'permissions' => [
                    'shipment.index',
                    'shipment.show',
                    'shipment.download manifest',
                    'doortodoor.download manifest',
                ],
            ],

            [
                'group_name' => 'Shipment Arrivals',
                'permissions' => [
                    'arrivals.index',
                    'arrivals.show',
                    'arrivals.download manifest',
                    'arrivals.unload',
                    'arrivals.mark as reached',
                ],
            ],

            [
                'group_name' => 'Bonded Warehouse',
                'permissions' => [
                    'bonded.index',
                    'bonded.show',
                    'bonded.mark as short loading',
                    'bonded.complete registration',
                ],
            ],

            [
                'group_name' => 'Unloading Issues',
                'permissions' => [
                    'issues.index',
                ],
            ],
        ];
    }
}
