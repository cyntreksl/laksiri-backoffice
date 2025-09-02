<?php

namespace App\Repositories\CallCenter;

use App\Actions\Queue\QueueCount;
use App\Http\Resources\CallCenter\CustomerQueueResource;
use App\Http\Resources\CallCenter\PackageQueueResource;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Models\CustomerQueue;
use App\Models\PackageQueue;
use App\Models\PackageReleaseLog;
use Illuminate\Http\JsonResponse;

class QueueRepository implements QueueRepositoryInterface
{
    public function getReceptionQueue(): JsonResponse
    {
        $queue = CustomerQueue::receptionQueue()->get();

        $customerQueues = CustomerQueueResource::collection($queue);

        return response()->json($customerQueues, 200);
    }

    public function getDocumentVerificationQueue(): JsonResponse
    {
        $queue = CustomerQueue::documentVerificationQueue()->get();

        $customerQueues = CustomerQueueResource::collection($queue);

        return response()->json($customerQueues, 200);
    }

    public function getReceptionQueueCounts(): JsonResponse
    {
        $queue = CustomerQueue::receptionQueue();

        return QueueCount::run($queue);
    }

    public function getDocumentVerificationQueueCounts(): JsonResponse
    {
        $queue = CustomerQueue::documentVerificationQueue();

        return QueueCount::run($queue);
    }

    public function getCashierQueue(): JsonResponse
    {
        $queue = CustomerQueue::cashierQueue()->get();

        $customerQueues = CustomerQueueResource::collection($queue);

        return response()->json($customerQueues, 200);
    }

    public function getCashierQueueCounts(): JsonResponse
    {
        $queue = CustomerQueue::cashierQueue();

        return QueueCount::run($queue);
    }

    public function getExaminationQueue(): JsonResponse
    {
        $queue = CustomerQueue::examinationQueue()->get();

        $customerQueues = CustomerQueueResource::collection($queue);

        return response()->json($customerQueues, 200);
    }

    public function getExaminationQueueCounts(): JsonResponse
    {
        $queue = CustomerQueue::examinationQueue();

        return QueueCount::run($queue);
    }

    public function getPackageQueue(): JsonResponse
    {
        $queue = PackageQueue::whereHas('token')->get();

        $packageQueues = PackageQueueResource::collection($queue);

        return response()->json($packageQueues, 200);
    }

    public function getPackageDetailsByToken(string $token): JsonResponse
    {
        $packageQueue = PackageQueue::whereHas('token', function ($query) use ($token) {
            $query->where('token', $token);
        })->with(['token.customer', 'releasedBy'])->first();

        if (! $packageQueue) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        return response()->json([
            'reference' => $packageQueue->reference,
            'customer' => $packageQueue->token->customer->name,
            'package_count' => $packageQueue->package_count,
            'released_at' => $packageQueue->released_at,
            'released_packages' => $packageQueue->released_packages,
        ]);
    }

    public function returnPackage(array $data): void
    {
        // Find the package queue by token number
        $packageQueue = PackageQueue::whereHas('token', function ($query) use ($data) {
            $query->where('token', $data['token_number']);
        })->first();

        if (! $packageQueue) {
            return; // Simply stop execution if not found
        }

        // Store the released packages before clearing them
        $releasedPackages = $packageQueue->released_packages ?? [];

        // Ensure we have valid package data
        if (empty($releasedPackages)) {
            $releasedPackages = [
                [
                    'reference' => $packageQueue->reference,
                    'package_count' => $packageQueue->package_count,
                    'returned_at' => now()->toDateTimeString(),
                ],
            ];
        }

        // Create a release log entry for the return
        PackageReleaseLog::create([
            'package_queue_id' => $packageQueue->id,
            'type' => 'return',
            'packages' => $releasedPackages,
            'remarks' => $data['remarks'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Update the package queue to mark as not released
        $packageQueue->update([
            'is_released' => false,
            'released_at' => null,
            'released_packages' => null,
            'note' => $data['remarks'] ?? null,
            'auth_id' => auth()->id(),
        ]);
    }

    public function getPackageLogs(int $packageQueueId): JsonResponse
    {
        $logs = PackageReleaseLog::where('package_queue_id', $packageQueueId)
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }
}
