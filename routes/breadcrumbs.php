<?php

use App\Models\Branch;
use App\Models\Container;
use App\Models\ExceptionName;
use App\Models\PickUp;
use App\Models\User;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use Spatie\Permission\Models\Role;

// Home
Breadcrumbs::for('dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('dashboard'));
});

// pickups
Breadcrumbs::for('pickups.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Pickup');
    $trail->push('Pending Jobs', route('pickups.index'));
});

Breadcrumbs::for('pickups.get-pending-jobs-by-user', function (BreadcrumbTrail $trail, string $userData) {
    $trail->parent('dashboard');
    $trail->push('Pickup', route('pickups.index'));
    $trail->push('Pending Jobs', route('pickups.get-pending-jobs-by-user', $userData));
});

// pickups > pickup ordering
Breadcrumbs::for('pickups.ordering', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Pickup', route('pickups.index'));
    $trail->push('Pickup Ordering', route('pickups.ordering'));
});

// pickups > create
Breadcrumbs::for('pickups.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Pickup', route('pickups.index'));
    $trail->push('Create Pickup', route('pickups.create'));
});

// pickups > edit
Breadcrumbs::for('pickups.edit', function (BreadcrumbTrail $trail, PickUp $pickup) {
    $trail->parent('dashboard');
    $trail->push('Pickup', route('pickups.index'));
    $trail->push('Edit Pickup', route('pickups.edit', $pickup));
});

// pickups > exceptions
Breadcrumbs::for('pickups.exceptions', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Pickup');
    $trail->push('Exceptions', route('pickups.exceptions'));
});

// HBLs
Breadcrumbs::for('hbls.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('HBL');
    $trail->push('HBL List', route('hbls.index'));
});

// HBL > create
Breadcrumbs::for('hbls.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('HBL', route('hbls.index'));
    $trail->push('HBL Create', route('hbls.create'));
});

// HBL > edit
Breadcrumbs::for('hbls.edit', function (BreadcrumbTrail $trail, $hbl) {
    $trail->parent('dashboard');
    $trail->push('HBL', route('hbls.index'));
    $trail->push('Edit HBL', route('hbls.edit', $hbl->id));
});

// HBL > Cancelled HBLs
Breadcrumbs::for('hbls.cancelled-hbls', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('HBL', route('hbls.index'));
    $trail->push('Cancelled HBL List', route('hbls.cancelled-hbls'));
});

// Users
Breadcrumbs::for('users.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('User Management', route('users.index'));
    $trail->push('System Users', route('users.index'));
});

// Users > Edit
Breadcrumbs::for('users.edit', function (BreadcrumbTrail $trail, User $user) {
    $trail->parent('users.index');
    $trail->push('User Edit', route('users.edit', $user->id));
});

// Warehouse Zone
Breadcrumbs::for('setting.warehouse-zones.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Warehouse Zones', route('setting.warehouse-zones.index'));
});

// Warehouse Zone > Edit
Breadcrumbs::for('setting.warehouse-zones.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('setting.warehouse-zones.index');
    $trail->push('Update Warehouse Zone', route('setting.warehouse-zones.edit', $id));
});

// Driver Area
Breadcrumbs::for('setting.driver-areas.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Driver Areas', route('setting.driver-areas.index'));
});

// Driver Area > Edit
Breadcrumbs::for('setting.driver-areas.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('setting.driver-areas.index');
    $trail->push('Settings');
    $trail->push('Update Driver Areas', route('setting.driver-areas.edit', $id));
});

// Drivers
Breadcrumbs::for('users.drivers.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('User Management', route('users.drivers.index'));
    $trail->push('Drivers', route('users.drivers.index'));
});
// Drivers > Edit
Breadcrumbs::for('users.drivers.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('users.drivers.index');
    $trail->push('Driver Edit', route('users.drivers.edit', $id));
});

//Cash settlement
Breadcrumbs::for('back-office.cash-settlements.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Back Office');
    $trail->push('Cash Settlement List', route('back-office.cash-settlements.index'));
});

// Settings > Zones
Breadcrumbs::for('setting.driver-zones.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Driver Zones', route('setting.driver-zones.index'));
});

// Loading > Container Index
Breadcrumbs::for('loading.loading-containers.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Loading');
    $trail->push('Containers List', route('loading.loading-containers.index'));
});

// Loading > Container Create
Breadcrumbs::for('loading.loading-containers.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Loading', route('loading.loading-containers.index'));
    $trail->push('Containers Create', route('loading.loading-containers.create'));
});

// Loading > Container Edit
Breadcrumbs::for('loading.loading-containers.edit', function (BreadcrumbTrail $trail, Container $container) {
    $trail->parent('dashboard');
    $trail->push('Loading', route('loading.loading-containers.index'));
    $trail->push('Container Edit', route('loading.loading-containers.edit', $container->id));
});

// Branches
Breadcrumbs::for('branches.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Branchs');
    $trail->push('List', route('branches.index'));
});

// Branches > Create
Breadcrumbs::for('branches.create', function (BreadcrumbTrail $trail) {
    $trail->parent('branches.index');
    $trail->push('Branch');
    $trail->push('Create', route('branches.create'));
});

// Branches > Edit
Breadcrumbs::for('branches.edit', function (BreadcrumbTrail $trail, Branch $branch) {
    $trail->parent('dashboard');
    $trail->push('Branch');
    $trail->push('Edit', route('branches.edit', $branch->id));
});

// Pricing
Breadcrumbs::for('setting.prices.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Price Rule List', route('setting.prices.index'));
});

// Branches > Create
Breadcrumbs::for('setting.prices.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Price Rule List', route('setting.prices.index'));
    $trail->push('Create', route('setting.prices.create'));
});

// Branches > Edit
Breadcrumbs::for('setting.prices.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Edit', route('setting.prices.edit', $id));
});

//Cash settlement
Breadcrumbs::for('back-office.warehouses.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Back Office');
    $trail->push('Warehouse List', route('back-office.warehouses.index'));
});

// Loaded Shipments
Breadcrumbs::for('loading.loaded-containers.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Loading');
    $trail->push('Loaded Shipments', route('loading.loaded-containers.index'));
});

// Roles
Breadcrumbs::for('users.roles.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('User Management', route('users.index'));
    $trail->push('Roles & Permissions', route('users.roles.index'));
});

Breadcrumbs::for('users.roles.create', function (BreadcrumbTrail $trail) {
    $trail->parent('users.roles.index');
    $trail->push('Create Role', route('users.roles.create'));
});

Breadcrumbs::for('users.roles.edit', function (BreadcrumbTrail $trail, Role $role) {
    $trail->parent('users.roles.index');
    $trail->push('Edit Role', route('users.roles.edit', $role->id));
});

// Shipments Arrivals
Breadcrumbs::for('arrival.shipments-arrivals.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Arrivals');
    $trail->push('Shipments Arrivals', route('arrival.shipments-arrivals.index'));
});

// Bonded Warehouse
Breadcrumbs::for('arrival.bonded-warehouses.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Arrivals');
    $trail->push('Bonded Warehouse', route('arrival.bonded-warehouses.index'));
});

// Unloading Issues
Breadcrumbs::for('arrival.unloading-issues.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Arrivals');
    $trail->push('Unloading Issues', route('arrival.unloading-issues.index'));
});

// Settings -> Exception Name
Breadcrumbs::for('setting.exception-names.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Exception Names', route('setting.exception-names.index'));
});

// Settings -> Exception Name
Breadcrumbs::for('setting.exception-names.edit', function (BreadcrumbTrail $trail, ExceptionName $exceptionName) {
    $trail->parent('setting.exception-names.index');
    $trail->push('Edit Exception Name', route('setting.exception-names.edit', $exceptionName));
});

require_once __DIR__.'/call-center-breadcrumbs.php';
