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
    $trail->push('Approve HBLs', route('finance.hbls.approve-hbl'));
});

// Approved HBLs
Breadcrumbs::for('finance.hbls.approved-hbl', function (BreadcrumbTrail $trail) {
    $trail->push('HBL');
    $trail->push('Approved HBLs', route('finance.hbls.approved-hbl'));
});
