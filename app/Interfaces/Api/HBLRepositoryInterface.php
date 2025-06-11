<?php

namespace App\Interfaces\Api;

use App\Models\HBL;
use Illuminate\Http\JsonResponse;

interface HBLRepositoryInterface
{
    public function showHBL(HBL $hbl): JsonResponse;

    public function storeHBL(array $data);

    public function calculatePayment(array $data): JsonResponse;

    public function getHBLRules(array $data);

    public function getCompletedHBL(array $data);

    public function completedHBLView(HBL $hbl);

    public function updateHBL(HBL $hbl, array $data): JsonResponse;

    public function getHBLTotalSummary(HBL $hbl);

    public function getHBLDestinationTotalSummary(HBL $hbl);
}
