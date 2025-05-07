<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHBLRequest;
use App\Interfaces\Api\HBLRepositoryInterface;
use App\Models\HBL;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HBLController extends Controller
{
    use ResponseAPI;

    public function __construct(
        private readonly HBLRepositoryInterface $HBLRepository,
    ) {}

    /**
     * Show HBL
     *
     * Display the specified HBL.
     *
     * @group HBL
     */
    public function show(HBL $hbl): JsonResponse
    {
        return $this->HBLRepository->showHBL($hbl);
    }

    /**
     * Store HBL
     *
     * Store a newly created HBL in storage.
     *
     * @group HBL
     */
    public function store(StoreHBLRequest $request)
    {
        return $this->HBLRepository->storeHBL($request->all());
    }

    /**
     * Calculate Payments
     *
     * Calculate HBL Package Payments.
     *
     * @group HBL
     */
    public function calculatePayment(Request $request)
    {
        return $this->HBLRepository->calculatePayment($request->all());
    }

    public function getHBLRules(Request $request)
    {
        return $this->HBLRepository->getHBLRules($request->all());
    }

    public function completedHBL(Request $request)
    {
        return $this->HBLRepository->getCompletedHBL($request->all());
    }

    public function completedHBLView(Request $request, $id)
    {
        $hbl = HBL::find($id);
        if (! $hbl) {
            return $this->error('HBL not found');
        }

        return $this->HBLRepository->completedHBLView($hbl);
    }
}
