<?php

namespace App\Interfaces\CallCenter;

use Illuminate\Http\JsonResponse;

interface QueueRepositoryInterface
{
    public function getDocumentVerificationQueue(): JsonResponse;

    public function getCashierQueue(): JsonResponse;

    public function getExaminationQueue(): JsonResponse;

    public function getPackageQueue(): JsonResponse;

    public function getDocumentVerificationQueueCounts(): JsonResponse;

    public function getCashierQueueCounts(): JsonResponse;

    public function getExaminationQueueCounts(): JsonResponse;

    // public function getPackageQueueCounts(): JsonResponse;

}
