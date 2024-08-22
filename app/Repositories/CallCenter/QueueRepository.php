<?php

namespace App\Repositories\CallCenter;

use App\Http\Resources\CallCenter\CustomerQueueResource;
use App\Http\Resources\CallCenter\PackageQueueResource;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Models\CustomerQueue;
use App\Models\PackageQueue;
use Illuminate\Http\JsonResponse;

class QueueRepository implements QueueRepositoryInterface
{
    public function getDocumentVerificationQueue(): JsonResponse
    {
        $queue = CustomerQueue::documentVerificationQueue()->get();

        $customerQueues = CustomerQueueResource::collection($queue);

        return response()->json($customerQueues, 200);
    }

    public function getCashierQueue(): JsonResponse
    {
        $queue = CustomerQueue::cashierQueue()->get();

        $customerQueues = CustomerQueueResource::collection($queue);

        return response()->json($customerQueues, 200);
    }

    public function getExaminationQueue(): JsonResponse
    {
        $queue = CustomerQueue::examinationQueue()->get();

        $customerQueues = CustomerQueueResource::collection($queue);

        return response()->json($customerQueues, 200);
    }

    public function getPackageQueue(): JsonResponse
    {
        $queue = PackageQueue::all();

        $packageQueues = PackageQueueResource::collection($queue);

        return response()->json($packageQueues, 200);
    }
}
