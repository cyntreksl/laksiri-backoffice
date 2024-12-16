<?php

namespace App\Interfaces;

use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLDocument;
use Illuminate\Http\JsonResponse;

interface OfficerRepositoryInterface
{
    public function getShippers();
    public function getConsignees();
}
