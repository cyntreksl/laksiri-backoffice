<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReturnPackageRequest;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use Inertia\Inertia;
use Illuminate\Http\Request;


class QueueController extends Controller
{
    public function __construct(
        private readonly QueueRepositoryInterface $queueRepository,
    ) {}

    public function showDocumentVerificationScreen()
    {
        return Inertia::render('CallCenter/Queue/VerificationScreen');
    }

    public function showDocumentCashierScreen()
    {
        return Inertia::render('CallCenter/Queue/CashierScreen');
    }

    public function showExaminationScreen()
    {
        return Inertia::render('CallCenter/Queue/ExaminationScreen');
    }

    public function showPackageScreen()
    {
        return Inertia::render('CallCenter/Queue/PackageScreen');
    }

    public function getDocumentVerificationQueue()
    {
        return $this->queueRepository->getDocumentVerificationQueue();
    }

    public function getCashierQueue()
    {
        return $this->queueRepository->getCashierQueue();
    }

    public function getExaminationQueue()
    {
        return $this->queueRepository->getExaminationQueue();
    }

    public function getPackageQueue()
    {
        return $this->queueRepository->getPackageQueue();
    }

    public function getPackageDetailsByToken($token): \Illuminate\Http\JsonResponse
    {
        return $this->queueRepository->getPackageDetailsByToken($token);
    }

    public function returnPackage(ReturnPackageRequest $request)
    {
        $this->queueRepository->returnPackage($request->validated());

    }


    public function getPackageLogs($packageQueueId)
    {
        return $this->queueRepository->getPackageLogs($packageQueueId);
    }
}
