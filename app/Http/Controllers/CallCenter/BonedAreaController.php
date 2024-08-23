<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use Inertia\Inertia;

class BonedAreaController extends Controller
{
    public function __construct(
        private readonly QueueRepositoryInterface $queueRepository,
    ) {
    }

    public function getPackageQueueList()
    {
        return Inertia::render('CallCenter/BonedArea/QueueList', [
            'packageQueue' => $this->queueRepository->getPackageQueue()->getData(),
        ]);
    }
}
