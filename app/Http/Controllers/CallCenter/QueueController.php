<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use Inertia\Inertia;

class QueueController extends Controller
{
    public function __construct(
        private readonly QueueRepositoryInterface $queueRepository,
    ) {
    }

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

    public function getDocumentVerificationQueue()
    {
        return $this->queueRepository->getDocumentVerificationQueue();
    }

    public function getCashierQueue()
    {
        return response()->json([]);
    }

    public function getExaminationQueue()
    {
        return response()->json([]);
    }
}
