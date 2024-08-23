<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\BonedAreaRepositoryInterface;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Models\PackageQueue;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BonedAreaController extends Controller
{
    public function __construct(
        private readonly QueueRepositoryInterface $queueRepository,
        private readonly BonedAreaRepositoryInterface $bonedAreaRepository,
    ) {
    }

    public function getPackageQueueList()
    {
        return Inertia::render('CallCenter/BonedArea/QueueList', [
            'packageQueue' => $this->queueRepository->getPackageQueue()->getData(),
        ]);
    }

    public function create(PackageQueue $packageQueue)
    {
        return Inertia::render('CallCenter/BonedArea/ReleaseForm', [
            'packageQueue' => $packageQueue,
        ]);
    }

    public function store(Request $request)
    {
        $this->bonedAreaRepository->releasePackage($request->all());
    }
}
