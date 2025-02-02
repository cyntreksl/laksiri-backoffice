<?php

namespace App\Interfaces\CallCenter;

use App\Models\Token;
use Illuminate\Http\JsonResponse;

interface ReceptionRepositoryInterface
{
    public function storeVerification(array $data): void;

    //    public function getHBLPaymentsDetails(Token $token): JsonResponse;
}
