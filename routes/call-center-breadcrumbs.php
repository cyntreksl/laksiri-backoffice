<?php

use App\Models\CustomerQueue;
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
    $trail->push('HBLs', route('call-center.hbls.index'));
});

// Reception
Breadcrumbs::for('call-center.reception.queue.hbl-list', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Reception');
    $trail->push('HBLs', route('call-center.reception.queue.hbl-list'));
});

Breadcrumbs::for('call-center.reception.show.verified', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Reception Verification');
    $trail->push('Verified', route('call-center.reception.show.verified'));
});

// Verification
Breadcrumbs::for('call-center.reception.queue.list', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Reception Verification');
    $trail->push('Queue', route('call-center.reception.queue.list'));
});

Breadcrumbs::for('call-center.reception.create', function (BreadcrumbTrail $trail, CustomerQueue $customerQueue) {
    $trail->parent('call-center.dashboard');
    $trail->push('Queue');
    $trail->push('Reception Verification', route('call-center.reception.create', $customerQueue));
});

// HBLs
Breadcrumbs::for('call-center.hbls.door-to-door-list', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('HBLs', route('call-center.hbls.index'));
    $trail->push('Door to Door HBLs', route('call-center.hbls.door-to-door-list'));
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

Breadcrumbs::for('call-center.examination.show.gate-pass', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Gate Pass');
    $trail->push('Released', route('call-center.examination.show.gate-pass'));
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

Breadcrumbs::for('call-center.package.show.released.list', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Boned Area');
    $trail->push('Released Packages', route('call-center.package.show.released.list'));
});

// Delivery Ordering
Breadcrumbs::for('delivery.ordering', function (BreadcrumbTrail $trail) {
    $trail->parent('call-center.dashboard');
    $trail->push('Delivery');
    $trail->push('Ordering', route('delivery.ordering'));
});
