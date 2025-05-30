<?php

use App\Models\VesselSchedule;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Vessel Schedule
Breadcrumbs::for('clearance.vessel-schedule.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Vessel Schedules', route('clearance.vessel-schedule.index'));
});

Breadcrumbs::for('clearance.vessel-schedule.show', function (BreadcrumbTrail $trail, VesselSchedule $vesselSchedule) {
    $trail->parent('clearance.vessel-schedule.index');
    $trail->push('Schedule', route('clearance.vessel-schedule.show', $vesselSchedule));
});
