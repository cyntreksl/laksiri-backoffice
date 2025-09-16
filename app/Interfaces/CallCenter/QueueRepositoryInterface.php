<?php

namespace App\Interfaces\CallCenter;

use Illuminate\Http\JsonResponse;

interface QueueRepositoryInterface
{
    public function getReceptionQueue(): JsonResponse;

    public function getDocumentVerificationQueue(): JsonResponse;

    public function getCashierQueue(): JsonResponse;

    public function getExaminationQueue(): JsonResponse;

    public function getPackageQueue(): JsonResponse;

    public function getReceptionQueueCounts(): JsonResponse;

    public function getDocumentVerificationQueueCounts(): JsonResponse;

    public function getCashierQueueCounts(): JsonResponse;

    public function getExaminationQueueCounts(): JsonResponse;

    // public function getPackageQueueCounts(): JsonResponse;

    public function getPackageDetailsByToken(string $token): JsonResponse;

    public function getPackagesForReturn(string $token): JsonResponse;

    public function returnPackage(array $data): void;

    public function getPackageLogs(int $packageQueueId): JsonResponse;
}
