<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\HBLType;
use Inertia\Inertia;

class MHBLController extends Controller
{
    public function create()
    {
        $this->authorize('hbls.create');

        dd("yes");

        return Inertia::render('MHBL/CreateMHBL', [
            'cargoTypes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
        ]);
    }
}
