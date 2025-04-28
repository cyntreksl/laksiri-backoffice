<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Vessel Schedule
Breadcrumbs::for('clearance.vessel-schedule.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Vessel Schedule', route('clearance.vessel-schedule.index'));
});
