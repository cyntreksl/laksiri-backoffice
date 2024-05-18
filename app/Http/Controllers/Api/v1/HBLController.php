<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHBLRequest;
use App\Interfaces\Api\HBLRepositoryInterface;
use Illuminate\Http\Request;

class HBLController extends Controller
{
    public function __construct(
        private readonly HBLRepositoryInterface $HBLRepository,
    )
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHBLRequest $request)
    {
        return $this->HBLRepository->storeHBL($request->all());
    }
}
