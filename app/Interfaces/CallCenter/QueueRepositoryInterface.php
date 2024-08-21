<?php

namespace App\Interfaces\CallCenter;

use Illuminate\Http\JsonResponse;

interface QueueRepositoryInterface
{
    public function getDocumentVerificationQueue(): JsonResponse;

    public function getCashierQueue(): JsonResponse;

    public function getExaminationQueue(): JsonResponse;
}
