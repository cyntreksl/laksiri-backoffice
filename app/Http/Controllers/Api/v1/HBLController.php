<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHBLRequest;
use App\Interfaces\Api\HBLRepositoryInterface;
use App\Models\HBL;
use Illuminate\Http\JsonResponse;

class HBLController extends Controller
{
    public function __construct(
        private readonly HBLRepositoryInterface $HBLRepository,
    ) {
    }

    public function show(HBL $hbl): JsonResponse
    {
        return $this->HBLRepository->showHBL($hbl);
    }

    public function store(StoreHBLRequest $request)
    {
        return $this->HBLRepository->storeHBL($request->all());
    }
}
