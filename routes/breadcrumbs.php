<?php

use App\Models\User;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

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

// pickups > pickup ordering
Breadcrumbs::for('pickups.ordering', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Pickup Ordering');
    $trail->push('Pickup Ordering', route('pickups.ordering'));
});

// pickups > create
Breadcrumbs::for('pickups.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Pickup');
    $trail->push('Create Pickup', route('pickups.create'));
});

Breadcrumbs::for('pickups.exceptions', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Pickups Exceptions');
    $trail->push('Exceptions', route('pickups.exceptions'));
});

// HBLs
Breadcrumbs::for('hbls.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('HBL List');
    $trail->push('HBL List', route('hbls.index'));
});

// HBL > create
Breadcrumbs::for('hbls.create', function (BreadcrumbTrail $trail) {
    $trail->parent('hbls.index');
    $trail->push('Create HBL', route('hbls.create'));
});

// Users
Breadcrumbs::for('users.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('User Management', route('users.index'));
});

// Users > Edit
Breadcrumbs::for('users.edit', function (BreadcrumbTrail $trail, User $user) {
    $trail->parent('users.index');
    $trail->push('User Edit', route('users.edit', $user->id));
});

// Drivers
Breadcrumbs::for('users.drivers.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Driver Management', route('users.drivers.index'));
});

//Cash settlement
Breadcrumbs::for('back-office.cash-settlements.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Cash Settlements');
    $trail->push('Settlement List', route('back-office.cash-settlements.index'));
});

// Settings > Zones
Breadcrumbs::for('settings.zones.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard'); // replace with 'settings.index'
    $trail->push('Zones', route('settings.zones.index'));
});

// Loading > Container Index
Breadcrumbs::for('loading.loading-containers.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard'); // replace with 'settings.index'
    $trail->push('Containers');
    $trail->push('Containers List', route('loading.loading-containers.index'));
});

// Loading > Container Create
Breadcrumbs::for('loading.loading-containers.create', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard'); // replace with 'settings.index'
    $trail->push('Containers', route('loading.loading-containers.index'));
    $trail->push('Containers Create', route('loading.loading-containers.create'));
});

// Settings > Branches
Breadcrumbs::for('setting.branches.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Settings');
    $trail->push('Branches', route('setting.branches.index'));
});

Breadcrumbs::for('setting.branches.create', function (BreadcrumbTrail $trail) {
    $trail->parent('setting.branches.index');
    $trail->push('Create', route('setting.branches.create'));
});
