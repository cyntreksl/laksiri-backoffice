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

        // Create roles with hierarchy (lower number = higher privilege)
        $rolesWithHierarchy = [
            'super-admin' => 0.00,  // optional top role if used
            'admin' => 1.00,

            'empty' => 10.00,
            'viewer' => 20.00,
            'driver' => 30.00,
            'customer' => 40.00,
            'call center' => 50.00,
            'boned area' => 60.00,
            'finance Team' => 70.00, // keep exact name used in app
            'front office staff' => 80.00,
            'clearance team' => 90.00,
            'gate-security' => 100.00,
        ];

        foreach ($rolesWithHierarchy as $name => $hierarchy) {
            Role::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['hierarchy' => $hierarchy]
            );
        }

        $this->command->info('Default Roles added with hierarchy.');

        // $this->assignPermissions();

        $this->createPermissionIfNotExsists();

    }

    protected function assignPermissions(): void
    {
        // Clear all existing permissions first
        Permission::query()->delete();

        $superAdminRole = Role::where('name', 'super-admin')->first();

        // Create all permissions and assign to super admin
        foreach (self::defaultPermissions() as $permissionGroup) {
            foreach ($permissionGroup['permissions'] as $permName) {
                $permission = Permission::updateOrCreate([
                    'name' => $permName,
                    'group_name' => $permissionGroup['group_name'],
                    'guard_name' => 'web',
                ]);
                $superAdminRole->givePermissionTo($permission);
            }
        }

        $adminRole = Role::where('name', 'admin')->first();

        $allowedAdminPermissionGroups = [
            'User',
            'Role',
            'Pickup',
            'HBL',
            'MHBL',
            'Cash Settlement',
            'Warehouse',
            'Container',
            'Loaded Shipment',
            'Unloading Issues',
            'Third Party Agent',
            'Courier',
            'Courier Agents',
            'Settings',
            'Third Party Shipment',
            'Token',
        ];

        $excludedAdminPermissions = [
            'air-line.index',
            'air-line.create',
            'air-line.list',
            'air-line.edit',
            'air-line.delete',
            'tax.departure tax',
            'tax.departure tax create',
            'tax.departure tax edit',
            'tax.departure tax delete',
            'tax.destination tax',
            'tax.destination tax create',
            'tax.destination tax edit',
            'tax.destination tax delete',
            'currencies.index',
            'currencies.create',
            'currencies.edit',
            'currencies.delete',
            'air-line.do charges index',
            'air-line.do charges create',
            'air-line.do charges list',
            'air-line.do charges edit',
            'air-line.do charges delete',
            'charges.special do charges index',
            'charges.special do charges create',
            'charges.special do charges list',
            'charges.special do charges edit',
            'charges.special do charges delete',
            'charges.air line do charges index',
            'charges.air line do charges create',
            'charges.air line do charges list',
            'charges.air line do charges edit',
            'charges.air line do charges delete',
        ];

        for ($i = 0; $i < count(self::defaultPermissions()); $i++) {
            $permissionGroup = self::defaultPermissions()[$i]['group_name'];
            for ($j = 0; $j < count(self::defaultPermissions()[$i]['permissions']); $j++) {
                $permission = Permission::updateOrCreate([
                    'name' => self::defaultPermissions()[$i]['permissions'][$j],
                    'group_name' => $permissionGroup,
                    'guard_name' => 'web',
                ]);
                if (in_array($permissionGroup, $allowedAdminPermissionGroups)) {
                    if (! in_array($permission->name, $excludedAdminPermissions)) {
                        $adminRole->givePermissionTo($permission);
                    }
                }
            }
        }

        $bonedAreaRole = Role::where('name', 'boned area')->first();

        $bonedAreaPermissions = [
            'hbls.index',
            'hbls.download pdf',
            'hbls.show',
            'container.index',
            'container.create',
            'container.load to container',
            'container.upload documents',
            'container.delete documents',
            'container.edit',
            'container.download documents',
            'shipment.index',
            'shipment.show',
            'shipment.download manifest',
            'doortodoor.download manifest',
            'arrivals.index',
            'arrivals.show',
            'arrivals.download manifest',
            'arrivals.unload',
            'arrivals.mark as reached',
            'bonded.index',
            'bonded.show',
            'bonded.mark as short loading',
            'bonded.complete registration',
            'issues.index',
            'customer-queue.show package calling queue',
            'customer-queue.show package released list',
            'customer-queue.show package calling screen',
        ];

        foreach ($bonedAreaPermissions as $permName) {
            $permission = Permission::where('name', $permName)->first();
            if ($permission) {
                $bonedAreaRole->givePermissionTo($permission);
            } else {
                $this->command->warn("Permission '{$permName}' not found.");
            }
        }

        $callCenterPermissions = [
            'hbls.index',
            'hbls.download pdf',
            'hbls.show',
            'hbls.issue token',
            'hbls.call flag',
            'hbls.baggage-receipt',
            'bonded.index',
            'bonded.show',
            'bonded.mark as short loading',
            'bonded.complete registration',
            'issues.index',
            'customer-queue.issue token',
            'customer-queue.show reception calling queue',
            'customer-queue.show reception verified list',
            'customer-queue.show reception calling screen',
            'customer-queue.show document verification queue',
            'customer-queue.show document verified list',
            'customer-queue.show document verification calling screen',
            'customer-queue.show package calling queue',
            'customer-queue.show package released list',
            'customer-queue.show package calling screen',
            'customer-queue.show cashier calling queue',
            'customer-queue.show cashier paid list',
            'customer-queue.show cashier calling screen',
            'customer-queue.show examination calling queue',
            'customer-queue.show gate ist',
            'customer-queue.show examination calling screen',
            'manage_tokens',
            'tokens.cancel',
            'call-center.hbl-list',
            'call-center.followups',
            'call-center.appointments',
            'call-center.all-calls',
        ];

        $callCenterRole = Role::where('name', 'call center')->first();

        foreach ($callCenterPermissions as $permName) {
            $permission = Permission::where('name', $permName)->first();
            if ($permission) {
                $callCenterRole->givePermissionTo($permission);
            } else {
                $this->command->warn("Permission '{$permName}' not found.");
            }
        }

        $financeTeamRole = Role::where('name', 'finance Team')->first();

        $financeTeamPermissions = [
            'air-line.index',
            'air-line.create',
            'air-line.list',
            'air-line.edit',
            'air-line.delete',

            'air-line.do charges index',
            'air-line.do charges create',
            'air-line.do charges list',
            'air-line.do charges edit',
            'air-line.do charges delete',

            'tax.departure tax',
            'tax.departure tax create',
            'tax.departure tax edit',
            'tax.departure tax delete',

            'tax.destination tax',
            'tax.destination tax create',
            'tax.destination tax edit',
            'tax.destination tax delete',

            'currencies.index',
            'currencies.create',
            'currencies.edit',
            'currencies.delete',

            'charges.special do charges index',
            'charges.special do charges create',
            'charges.special do charges list',
            'charges.special do charges edit',
            'charges.special do charges delete',

            'charges.air line do charges index',
            'charges.air line do charges create',
            'charges.air line do charges list',
            'charges.air line do charges edit',
            'charges.air line do charges delete',

            'hbls.index',
            'hbls.show',
            'hbls.hold and release',
            'hbls.download pdf',
            'hbls.download invoice',
            'hbls.download barcode',
            'hbls.hbl finance approval list',
            'hbls.finance approved hbl list',
            'hbls.create finance approval',

            'arrivals.index',
            'arrivals.show',
            'arrivals.download manifest',

            'vessel.schedule.index',

            'payment-container.index',
            'payment-container.refund list',
            'payment-container.show container payment requests',
            'payment-container.approve',
            'payment-container.approved list',
            'payment-container.collect refund',
            'payment-container.completed payment requests',
        ];

        foreach ($financeTeamPermissions as $permName) {
            $permission = Permission::where('name', $permName)->first();
            if ($permission) {
                $financeTeamRole->givePermissionTo($permission);
            } else {
                $this->command->warn("Permission '{$permName}' not found.");
            }
        }

        $clearanceTeamRole = Role::where('name', 'clearance team')->first();

        $clearanceTeamPermissions = [
            'hbls.index',
            'hbls.show',
            'hbls.hold and release',
            'hbls.download pdf',
            'hbls.download invoice',
            'hbls.download barcode',

            'arrivals.index',
            'arrivals.show',
            'arrivals.download manifest',

            'vessel.schedule.index',

            'payment-container.index',
            'payment-container.create',
            'payment-container.edit',
            'payment-container.delete',
            'payment-container.refund list',
            'payment-container.show container payment requests',
            'payment-container.approved list',
            'payment-container.completed payment requests',
        ];

        foreach ($clearanceTeamPermissions as $permName) {
            $permission = Permission::where('name', $permName)->first();
            if ($permission) {
                $clearanceTeamRole->givePermissionTo($permission);
            } else {
                $this->command->warn("Permission '{$permName}' not found.");
            }
        }

        $securityRole = Role::where('name', 'gate-security')->first();

        $securityPermissions = [
            'mark-shipment-arrived-to-warehouse',
            'mark-shipment-depart-from-warehouse',
            'mark-gate-pass',
        ];

        foreach ($securityPermissions as $permName) {
            $permission = Permission::where('name', $permName)->first();
            if ($permission) {
                $securityRole->givePermissionTo($permission);
            } else {
                $this->command->warn("Permission '{$permName}' not found.");
            }
        }

        $this->command->info('Permissions assigned to admin and boned area roles.');
    }

    public static function defaultPermissions(): array
    {
        return [
            [
                'group_name' => 'Settings',
                'permissions' => [
                    'pickup-type.create',
                    'pickup-type.show',
                    'pickup-type.edit',
                    'pickup-type.delete',
                    'pickup-type.index',

                    'air-line.index',
                    'air-line.create',
                    'air-line.list',
                    'air-line.edit',
                    'air-line.delete',

                    'air-line.do charges index',
                    'air-line.do charges create',
                    'air-line.do charges list',
                    'air-line.do charges edit',
                    'air-line.do charges delete',

                    'tax.departure tax',
                    'tax.departure tax create',
                    'tax.departure tax edit',
                    'tax.departure tax delete',

                    'tax.destination tax',
                    'tax.destination tax create',
                    'tax.destination tax edit',
                    'tax.destination tax delete',

                    'currencies.index',
                    'currencies.create',
                    'currencies.edit',
                    'currencies.delete',

                    'charges.special do charges index',
                    'charges.special do charges create',
                    'charges.special do charges list',
                    'charges.special do charges edit',
                    'charges.special do charges delete',

                    'charges.air line do charges index',
                    'charges.air line do charges create',
                    'charges.air line do charges list',
                    'charges.air line do charges edit',
                    'charges.air line do charges delete',

                    'manage_zones',

                    'manage_driver_zones',

                    'manage_driver_areas',

                    'manage_warehouse_zones',

                    'manage_pricing',

                    'manage_package_pricing',

                    'manage_exceptions',

                    'manage_package_types',

                    'manage_shippers_and_consignees',
                ],
            ],

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
                'group_name' => 'Branch',
                'permissions' => [
                    'branches.create',
                    'branches.list',
                    'branches.edit',
                    'branches.delete',
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
                    'pickups.unassign driver',
                    'pickups.show pickup order',
                    'pickups.update pickup order',
                    'pickups.show pickup exceptions',
                    'pickups.retry',
                    'pickups.index',
                    'pickups.trash_pickups',
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
                    'hbls.show door to door list',
                    'hbls.restore',
                    'hbls.download pdf',
                    'hbls.download invoice',
                    'hbls.download barcode',
                    'hbls.upload documents',
                    'hbls.delete documents',
                    'hbls.issue token',
                    'hbls.show draft hbls',
                    'hbls.hbl finance approval list',
                    'hbls.finance approved hbl list',
                    'hbls.create finance approval',
                    'hbls.call flag',
                    'hbls.baggage-receipt',
                ],
            ],

            [
                'group_name' => 'MHBL',
                'permissions' => [
                    'mhbls.index',
                    'mhbls.download hbl list',
                ],
            ],

            [
                'group_name' => 'Delivers',
                'permissions' => [
                    'delivers.assign release to driver',
                    'delivers.show deliver order',
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
                    'warehouse.update payment',
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
                    'all.shipments.index',
                    'all.shipments.show',
                    'all.shipments.download manifest',
                ],
            ],

            [
                'group_name' => 'Loaded Shipment',
                'permissions' => [
                    'shipment.index',
                    'shipment.show',
                    'shipment.download manifest',
                    'doortodoor.download manifest',
                    'shipment.download tally sheet',
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

            [
                'group_name' => 'Customer Queue',
                'permissions' => [
                    'customer-queue.issue token',
                    'customer-queue.show reception calling queue',
                    'customer-queue.show reception verified list',
                    'customer-queue.show reception calling screen',
                    'customer-queue.show document verification queue',
                    'customer-queue.show document verified list',
                    'customer-queue.show document verification calling screen',
                    'customer-queue.show package calling queue',
                    'customer-queue.show package released list',
                    'customer-queue.show package calling screen',
                    'customer-queue.show cashier calling queue',
                    'customer-queue.show cashier paid list',
                    'customer-queue.show cashier calling screen',
                    'customer-queue.show examination calling queue',
                    'customer-queue.show gate ist',
                    'customer-queue.show examination calling screen',
                ],
            ],

            [
                'group_name' => 'Third Party Agent',
                'permissions' => [
                    'third-party-agents.index',
                    'third-party-agents.create',
                    'third-party-agents.edit',
                    'third-party-agents.delete',
                ],
            ],

            [
                'group_name' => 'Courier',
                'permissions' => [
                    'courier.index',
                    'courier.create',
                    'courier.edit',
                    'courier.delete',
                    'courier.download pdf',
                    'courier.download invoice',
                ],
            ],

            [
                'group_name' => 'Courier Agents',
                'permissions' => [
                    'courier-agents.index',
                    'courier-agents.create',
                    'courier-agents.edit',
                    'courier-agents.delete',
                ],
            ],

            [
                'group_name' => 'Vessel Schedule',
                'permissions' => [
                    'vessel.schedule.index',
                ],
            ],

            [
                'group_name' => 'Payment Container',
                'permissions' => [
                    'payment-container.index',
                    'payment-container.create',
                    'payment-container.edit',
                    'payment-container.delete',
                    'payment-container.refund list',
                    'payment-container.show container payment requests',
                    'payment-container.approve',
                    'payment-container.approved list',
                    'payment-container.collect refund',
                    'payment-container.completed payment requests',
                ],
            ],

            [
                'group_name' => 'Warehouse Gate Control',
                'permissions' => [
                    'mark-shipment-arrived-to-warehouse',
                    'mark-shipment-depart-from-warehouse',
                    'mark-gate-pass',
                ],
            ],

            // reception permissions
            [
                'group_name' => 'Reception',
                'permissions' => [
                    'reception.show reception calling queue',
                    'reception.show reception verified list',
                    'reception.show reception calling screen',
                    'reception.issue token',
                ],
            ],

            [
                'group_name' => 'Token',
                'permissions' => [
                    'manage_tokens',
                    'tokens.cancel',
                ],
            ],

            [
                'group_name' => 'Third Party Shipment',
                'permissions' => [
                    'third_party_shipments.index',
                    'third_party_shipments.create',
                    'third_party_shipments.edit',
                    'third_party_shipments.delete',
                    'third_party_shipments.show',
                ],
            ],

            [
                'group_name' => 'Call Center',
                'permissions' => [
                    'call-center.hbl-list',
                    'call-center.followups',
                    'call-center.appointments',
                    'call-center.all-calls',
                ],
            ],

            [
                'group_name' => 'RTF',
                'permissions' => [
                    'set_rtf',
                    'lift_rtf',
                ],
            ],

            [
                'group_name' => 'Whatsapp',
                'permissions' => [
                    'manage_whatsapp',
                ],
            ],

            [
                'group_name' => 'Reports',
                'permissions' => [
                    'reports.detain',
                ],
            ],
        ];
    }

    public function createPermissionIfNotExsists()
    {
        foreach (self::defaultPermissions() as $permissionGroup) {
            foreach ($permissionGroup['permissions'] as $permName) {
                $permission = Permission::updateOrCreate([
                    'name' => $permName,
                    'group_name' => $permissionGroup['group_name'],
                    'guard_name' => 'web',
                ]);
            }

            $this->command->info("Permission group '{$permissionGroup['group_name']}' with permissions created or updated.");
        }
    }
}
