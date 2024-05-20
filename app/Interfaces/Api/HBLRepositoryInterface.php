<?php

namespace App\Interfaces\Api;

use App\Models\HBL;
use Illuminate\Http\JsonResponse;

interface HBLRepositoryInterface
{
    public function showHBL(HBL $hbl): JsonResponse;
interface HBLRepositoryInterface
{
    public function storeHBL(array $data);
}
