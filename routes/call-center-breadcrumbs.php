<?php

use App\Models\CustomerQueue;
use App\Models\PackageQueue;
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

// HBLs
Breadcrumbs::for('call-center.hbls.door-to-door-list', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('HBL', route('call-center.hbls.index'));
    $trail->push('Door to Door HBL List', route('call-center.hbls.door-to-door-list'));
});

// Verification
Breadcrumbs::for('call-center.verification.queue.list', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Document Verification');
    $trail->push('Queue', route('call-center.verification.queue.list'));
});

Breadcrumbs::for('call-center.verification.create', function (BreadcrumbTrail $trail, CustomerQueue $customerQueue) {
    $trail->parent('call-center.dashboard');
    $trail->push('Queue');
    $trail->push('Document Verification', route('call-center.verification.create', $customerQueue));
});

Breadcrumbs::for('call-center.verification.show.verified', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Document Verification');
    $trail->push('Verified', route('call-center.verification.show.verified'));
});

// Cashier
Breadcrumbs::for('call-center.cashier.queue.list', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Cashier');
    $trail->push('Queue', route('call-center.cashier.queue.list'));
});

Breadcrumbs::for('call-center.cashier.create', function (BreadcrumbTrail $trail, CustomerQueue $customerQueue) {
    $trail->parent('call-center.dashboard');
    $trail->push('Cashier');
    $trail->push('Settle Payment', route('call-center.cashier.create', $customerQueue));
});

Breadcrumbs::for('call-center.cashier.show.paid', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Cashier');
    $trail->push('Paid', route('call-center.cashier.show.paid'));
});

// Examination
Breadcrumbs::for('call-center.examination.queue.list', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Examination');
    $trail->push('Queue', route('call-center.examination.queue.list'));
});

Breadcrumbs::for('call-center.examination.create', function (BreadcrumbTrail $trail, CustomerQueue $customerQueue) {
    $trail->parent('call-center.dashboard');
    $trail->push('Examination');
    $trail->push('Release HBL Packages', route('call-center.examination.create', $customerQueue));
});

// Boned Area
Breadcrumbs::for('call-center.package.queue.list', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Boned Area');
    $trail->push('Package Queue', route('call-center.package.queue.list'));
});

Breadcrumbs::for('call-center.package.create', function (BreadcrumbTrail $trail, PackageQueue $packageQueue) {
    $trail->parent('call-center.dashboard');
    $trail->push('Boned Area');
    $trail->push('Package Release', route('call-center.package.create', $packageQueue));
});

Breadcrumbs::for('call-center.package.show.released.list', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Boned Area');
    $trail->push('Released Packages', route('call-center.package.show.released.list'));
});

//Delivery Ordering
Breadcrumbs::for('delivery.ordering', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Delivery');
    $trail->push('Ordering', route('delivery.ordering'));
});
