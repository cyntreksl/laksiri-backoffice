<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use Inertia\Inertia;

class ExaminationController extends Controller
{
    public function __construct(
        private readonly QueueRepositoryInterface $queueRepository,
    ) {
    }

    public function getExaminationQueueList()
    {
        return Inertia::render('CallCenter/Examination/QueueList', [
            'examinationQueue' => $this->queueRepository->getExaminationQueue()->getData(),
        ]);
    }
}
