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
    $trail->push('HBL List', route('call-center.hbls.index'));
});

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
