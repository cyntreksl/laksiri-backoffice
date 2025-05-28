<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// HBLs
Breadcrumbs::for('finance.hbls.index', function (BreadcrumbTrail $trail) {
    $trail->push('HBL');
    $trail->push('HBL List', route('finance.hbls.index'));
});

// Approve HBLs
Breadcrumbs::for('finance.hbls.approve-hbl', function (BreadcrumbTrail $trail) {
    $trail->push('HBL');
    $trail->push('HBL Approval', route('finance.hbls.approve-hbl'));
});

// Approved HBLs
Breadcrumbs::for('finance.hbls.approved-hbl', function (BreadcrumbTrail $trail) {
    $trail->push('HBL');
    $trail->push('Approved HBLs', route('finance.hbls.approved-hbl'));
});

// Container Payment
Breadcrumbs::for('finance.container-payments.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Container Payment');
    $trail->push('Requests List', route('finance.container-payments.index'));
});

// Container Payment
Breadcrumbs::for('finance.approved-container-payments', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Container Payment');
    $trail->push('Approved List', route('finance.approved-container-payments'));
});
