<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('dashboard'));
});

// pickups
Breadcrumbs::for('pickups.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Pickups', route('pickups.index'));
});

// pickups > create
Breadcrumbs::for('pickups.create', function (BreadcrumbTrail $trail) {
    $trail->parent('pickups.index');
    $trail->push('Create Pickup', route('pickups.create'));
});
