<?php

namespace App\Http\Controllers;

use App\Http\Resources\RemarksResource;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLPackage;

class RemarksController extends Controller
{
    public function index($type, $id)
    {
        $modelClass = match ($type) {
            'hbl' => HBL::class,
            'container' => Container::class,
            'package' => HBLPackage::class,
        };

        $model = $modelClass::findOrFail($id);

        return RemarksResource::collection($model->remarks()->with('user:id,name')->get());
    }
}
