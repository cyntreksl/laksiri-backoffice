<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class QueueController extends Controller
{
    public function index()
    {
        return Inertia::render('CallCenter/Queue/QueueList');
    }
}
