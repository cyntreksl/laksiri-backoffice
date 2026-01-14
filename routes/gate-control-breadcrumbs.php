<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Gate Control - Complete Token
Breadcrumbs::for('gate-control.complete-token', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Gate Control');
    $trail->push('Complete Token', route('gate-control.complete-token'));
});

// Gate Control - Completed Tokens List
Breadcrumbs::for('gate-control.completed-tokens.index', function (BreadcrumbTrail $trail) {
    $trail->parent('dashboard');
    $trail->push('Gate Control');
    $trail->push('Completed Tokens', route('gate-control.completed-tokens.index'));
});
