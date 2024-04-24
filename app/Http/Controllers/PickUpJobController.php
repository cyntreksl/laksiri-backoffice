<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class PickUpJobController extends Controller
{
    public function create()
    {
        return Inertia::render('Pickup/CreateJob');
    }
}
