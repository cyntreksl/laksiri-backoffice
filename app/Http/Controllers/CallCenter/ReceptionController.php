<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReceptionController extends Controller
{
    public function __construct(
        private readonly QueueRepositoryInterface $queueRepository,
    ) {
    }

    public function getReceptionQueueList()
    {
        return Inertia::render('CallCenter/Reception/QueueList', [
            'receptionQueue' => $this->queueRepository->getReceptionQueue()->getData(),
            'receptionQueueCounts' => $this->queueRepository->getReceptionQueueCounts()->getData(),
        ]);
    }

    public function getReceptionList(Request $request)
    {
        //        $limit = $request->input('limit', 10);
        //        $page = $request->input('offset', 1);
        //        $order = $request->input('order', 'id');
        //        $dir = $request->input('dir', 'asc');
        //
        //        return $this->verificationRepository->dataset($limit, $page, $order, $dir);
    }
}
