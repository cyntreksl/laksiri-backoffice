<?php

namespace App\Interfaces\CallCenter;

use Illuminate\Http\JsonResponse;

interface QueueRepositoryInterface
{
    public function getDocumentVerificationQueue(): JsonResponse;
}
