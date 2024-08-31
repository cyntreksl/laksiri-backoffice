<?php

namespace App\Interfaces\Api;

use App\Models\HBL;
use Illuminate\Http\JsonResponse;

interface HBLRepositoryInterface
{
    public function showHBL(HBL $hbl): JsonResponse;

    public function storeHBL(array $data);

    public function calculatePayment(array $data): JsonResponse;
}
