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
    $trail->push('Pending Jobs');
    $trail->push('Pending Jobs', route('pickups.index'));
});

// pickups > create
Breadcrumbs::for('pickups.create', function (BreadcrumbTrail $trail) {
    $trail->parent('pickups.index');
    $trail->push('Create Pickup', route('pickups.create'));
});

// HBLs
Breadcrumbs::for('hbls.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
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
