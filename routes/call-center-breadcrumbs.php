<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home
Breadcrumbs::for('call-center.dashboard', function (BreadcrumbTrail $trail) {
    $trail->push('Home', route('call-center.dashboard'));
});

// HBLs
Breadcrumbs::for('call-center.hbls.index', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('HBL');
    $trail->push('HBL List', route('call-center.hbls.index'));
});
