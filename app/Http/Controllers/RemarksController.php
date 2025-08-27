<?php

namespace App\Http\Controllers;

use App\Http\Resources\RemarksResource;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HblPackage;

class RemarksController extends Controller
{
    public function index($type, $id)
    {
        $modelClass = match ($type) {
            'hbl' => HBL::class,
            'container' => Container::class,
            'package' => HblPackage::class,
        };

        dd($id);

        $model = $modelClass::findOrFail($id);

        return RemarksResource::collection($model->remarks()->with('user:id,name')->get());
    }
}
