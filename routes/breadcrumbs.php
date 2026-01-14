<?php

use App\Models\Branch;
use App\Models\Container;
use App\Models\ExceptionName;
use App\Models\MHBL;
use App\Models\PackageType;
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
// pickups >all pickups
Breadcrumbs::for('pickups.all', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Pickup');
    $trail->push('All Pickups', route('pickups.all'));
});

// HBLs
Breadcrumbs::for('hbls.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('HBL');
    $trail->push('HBLs', route('hbls.index'));
});

Breadcrumbs::for('hbls.door-to-door-list', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('HBL');
    $trail->push('Door to Door HBLs', route('hbls.door-to-door-list'));
});

Breadcrumbs::for('hbls.draft-list', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('HBL');
    $trail->push('Draft HBLs', route('hbls.draft-list'));
});

Breadcrumbs::for('hbls.get-hbls-by-user', function (BreadcrumbTrail $trail, string $userData) {
    $trail->parent('dashboard');
    $trail->push('HBLs', route('hbls.index'));
    $trail->push('HBLs', route('hbls.get-hbls-by-user', $userData));
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
    $trail->push('Cancelled HBLs', route('hbls.cancelled-hbls'));
});

Breadcrumbs::for('hbls.status-default', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('HBL', route('hbls.index'));
    $trail->push('HBL Status Default', route('hbls.status-default'));
});

// MHBL > create
Breadcrumbs::for('mhbls.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('MHBL', route('mhbls.index'));
    $trail->push('MHBL Create', route('mhbls.create'));
});

// Users
Breadcrumbs::for('users.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('User Management', route('users.index'));
    $trail->push('System Users', route('users.index'));
});

// MHBLs
Breadcrumbs::for('mhbls.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('MHBL');
    $trail->push('MHBLs', route('mhbls.index'));
});

// Users > Edit
Breadcrumbs::for('mhbls.edit', function (BreadcrumbTrail $trail, MHBL $mhbl) {
    $trail->parent('dashboard');
    $trail->push('MHBL');
    $trail->push('MHBL Edit', route('mhbls.edit', $mhbl->id));
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

// Air Lines
Breadcrumbs::for('setting.air-lines.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Air Lines', route('setting.air-lines.index'));
});

// Special DO Charges
Breadcrumbs::for('setting.special-do-charges.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Special DO Charges', route('setting.special-do-charges.index'));
});

Breadcrumbs::for('setting.special-do-charges.create', function (BreadcrumbTrail $trail) {
    $trail->parent('setting.special-do-charges.index');
    $trail->push('Create Special DO Charges', route('setting.special-do-charges.create'));
});

Breadcrumbs::for('setting.special-do-charges.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('setting.special-do-charges.index');
    $trail->push('Edit Special DO Charge');
});

// Currencies
Breadcrumbs::for('setting.currencies.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Currency Rates', route('setting.currencies.index'));
});

// Driver Area
Breadcrumbs::for('setting.driver-areas.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Driver Areas', route('setting.driver-areas.index'));
});

// Taxes
Breadcrumbs::for('setting.taxes.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Taxes', route('setting.taxes.index'));
});

// Air Line DO Charge
Breadcrumbs::for('setting.air-lines.do-charges', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Air Lines DO Charges', route('setting.air-lines.do-charges'));
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

Breadcrumbs::for('users.customers.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('User Management', route('users.customers.index'));
    $trail->push('Customers', route('users.customers.index'));
});

// Cash settlement
Breadcrumbs::for('back-office.cash-settlements.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Back Office');
    $trail->push('Cash Settlements', route('back-office.cash-settlements.index'));
});
// due payment
Breadcrumbs::for('back-office.duepayments.duePaymentIndex', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Back Office');
    $trail->push('Due Payments', route('back-office.duepayments.duePaymentIndex'));
});

// Settings > Zones
Breadcrumbs::for('setting.driver-zones.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Driver Zones', route('setting.driver-zones.index'));
});

Breadcrumbs::for('loading.all-shipments', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Loading');
    $trail->push('All Shipments', route('loading.all-shipments'));
});

// Loading > Shipments Index
Breadcrumbs::for('loading.loading-containers.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Loading');
    $trail->push('Shipments', route('loading.loading-containers.index'));
});

// Loading > Shipments Create
Breadcrumbs::for('loading.loading-containers.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Loading', route('loading.loading-containers.index'));
    $trail->push('Shipments Create', route('loading.loading-containers.create'));
});

// Loading > Shipments Edit
Breadcrumbs::for('loading.loading-containers.edit', function (BreadcrumbTrail $trail, Container $container) {
    $trail->parent('dashboard');
    $trail->push('Loading', route('loading.loading-containers.index'));
    $trail->push('Shipments Edit', route('loading.loading-containers.edit', $container->id));
});

// Branches
Breadcrumbs::for('branches.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Branches', route('branches.index'));
});

// Branches > Create
Breadcrumbs::for('branches.create', function (BreadcrumbTrail $trail) {
    $trail->parent('branches.index');
    $trail->push('Create Branch', route('branches.create'));
});

// Branches > Edit
Breadcrumbs::for('branches.edit', function (BreadcrumbTrail $trail, Branch $branch) {
    $trail->parent('dashboard');
    $trail->push('Branch');
    $trail->push('Configurations', route('branches.edit', $branch->id));
});

// Pricing
Breadcrumbs::for('setting.prices.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Price Rules', route('setting.prices.index'));
});

Breadcrumbs::for('setting.prices.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Price Rules', route('setting.prices.index'));
    $trail->push('Create Price Rule');
});

Breadcrumbs::for('setting.prices.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Price Rules', route('setting.prices.index'));
    $trail->push('Edit Price Rule');
});

// Cash settlement
Breadcrumbs::for('back-office.warehouses.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Back Office');
    $trail->push('Warehouses', route('back-office.warehouses.index'));
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

// Package Pricing
Breadcrumbs::for('setting.package-prices.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Package Price Rules', route('setting.package-prices.index'));
});

// Package Rule > create
Breadcrumbs::for('setting.package-prices.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Package Price Rules', route('setting.package-prices.index'));
    $trail->push('Create Package Rule', route('setting.package-prices.create'));
});

// Package Rule > Edit
Breadcrumbs::for('setting.package-prices.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Package Price Rules', route('setting.package-prices.index', $id));
    $trail->push('Edit', route('setting.package-prices.edit', $id));
});

// Settings -> Package Type
Breadcrumbs::for('setting.package-types.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Package Types', route('setting.package-types.index'));
});

// Settings -> Package Type Edit
Breadcrumbs::for('setting.package-types.edit', function (BreadcrumbTrail $trail, PackageType $packageType) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Package Types', route('setting.package-types.index'));
    $trail->push('Edit Package Type', route('setting.package-types.edit', $packageType));
});

Breadcrumbs::for('setting.shipper-consignees.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Shipper & Consignees', route('setting.shipper-consignees.index'));
});

// Settings -> Package Type Edit
Breadcrumbs::for('setting.shipper-consignees.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Shipper & Consignees', route('setting.shipper-consignees.index'));
    $trail->push('Edit Officer', route('setting.shipper-consignees.edit', $id));
});

// Courier -> Third Party Agents List
Breadcrumbs::for('couriers.agents.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Couriers');
    $trail->push('Third Party Agents', route('couriers.agents.index'));
});

// Courier -> Third Party Agents Create
Breadcrumbs::for('couriers.agents.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Couriers');
    $trail->push('Third Party Agents', route('couriers.agents.index'));
    $trail->push('Create Third Party Agent', route('couriers.agents.create'));
});

// Courier -> Third Party Agents Edit
Breadcrumbs::for('couriers.agents.edit', function (BreadcrumbTrail $trail, $id) {
    $branch = Branch::find($id);
    $trail->parent('dashboard');
    $trail->push('Couriers');
    $trail->push('Third Party Agents', route('couriers.agents.index'));
    $trail->push('Edit Third Party Agent', route('couriers.agents.edit', $branch->id));
});

// Courier
Breadcrumbs::for('couriers.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Couriers');
    $trail->push('List', route('couriers.index'));
});

// Couriers > Create
Breadcrumbs::for('couriers.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Couriers', route('couriers.index'));
    $trail->push('Create Courier', route('couriers.create'));
});

// Couriers > edit
Breadcrumbs::for('couriers.edit', function (BreadcrumbTrail $trail, $courier) {
    $trail->parent('dashboard');
    $trail->push('Couriers', route('couriers.index'));
    $trail->push('Edit Courier', route('couriers.edit', $courier->id));
});

// Courier Agents
Breadcrumbs::for('couriers.courier-agents.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Couriers');
    $trail->push('Courier Agents', route('couriers.courier-agents.index'));
});

Breadcrumbs::for('couriers.courier-agents.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Courier');
    $trail->push('Courier Agents', route('couriers.courier-agents.index'));
    $trail->push('Create', route('couriers.courier-agents.create'));
});

Breadcrumbs::for('couriers.courier-agents.edit', function (BreadcrumbTrail $trail, $id) {
    $trail->parent('dashboard');
    $trail->push('Courier');
    $trail->push('Courier Agents', route('couriers.courier-agents.index'));
    $trail->push('Edit', route('couriers.courier-agents.edit', $id));
});

// Settings -> Exception Name
Breadcrumbs::for('setting.pickup-types.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Pickup Types', route('setting.pickup-types.index'));
});

// Container Payment
Breadcrumbs::for('container-payment.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Container Payment');
    $trail->push('List', route('container-payment.index'));
});

// Container Payment Refunds
Breadcrumbs::for('container-payment.showContainerPaymentRefund', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Container Payment');
    $trail->push('Refunds', route('container-payment.showContainerPaymentRefund'));
});

// Completed Container Payment
Breadcrumbs::for('container-payment.showCompletedContainerPayment', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Container Payments');
    $trail->push('Completed', route('container-payment.showCompletedContainerPayment'));
});

Breadcrumbs::for('container-payment.request', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Container Payments');
    $trail->push('Requests', route('container-payment.request'));
});

Breadcrumbs::for('approved-container-payments', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Container Payments');
    $trail->push('Approved Payments', route('approved-container-payments'));
});

// Gate Control
Breadcrumbs::for('gate-control.inbound-shipments.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Gate Control');
    $trail->push('Inbound Shipments', route('gate-control.inbound-shipments.index'));
});

Breadcrumbs::for('gate-control.outbound-shipments.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Gate Control');
    $trail->push('Outbound Containers', route('gate-control.outbound-shipments.index'));
});

Breadcrumbs::for('gate-control.outbound-gate-passes.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Gate Control');
    $trail->push('Outbound Gate Pass', route('gate-control.outbound-gate-passes.index'));
});

Breadcrumbs::for('call-center.tokens.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Tokens', route('call-center.tokens.index'));
});

Breadcrumbs::for('call-center.tokens.show', function (BreadcrumbTrail $trail, \App\Models\Token $token) {
    $trail->parent('call-center.tokens.index');
    $trail->push('Token Details', route('call-center.tokens.show', $token->id));
});

Breadcrumbs::for('third-party-shipments.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Create Third Party Shipment', route('third-party-shipments.create'));
});

Breadcrumbs::for('third-party-shipments.create.v2', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Create Third Party Shipment', route('third-party-shipments.create.v2'));
});

require_once __DIR__.'/call-center-breadcrumbs.php';
require_once __DIR__.'/finance-breadcrumbs.php';
require_once __DIR__.'/clearance-breadcrumbs.php';
require_once __DIR__.'/gate-control-breadcrumbs.php';
