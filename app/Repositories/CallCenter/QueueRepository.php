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

    public function getPackagesForReturn(string $token): JsonResponse
    {
        // Get all package queues for this token (including historical ones)
        $packageQueues = PackageQueue::whereHas('token', function ($query) use ($token) {
            $query->where('token', $token);
        })
            ->with([
                'token.customer',
                'token.hbl',
                'releasedBy',
                'releaseLogs' => function ($query) {
                    $query->with('createdBy')->orderBy('created_at', 'desc');
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($packageQueues->isEmpty()) {
            return response()->json(['error' => 'No packages found for this token'], 404);
        }

        $response = [];
        foreach ($packageQueues as $packageQueue) {
            $hbl = $packageQueue->token->hbl;
            $hblReference = $hbl ? $hbl->reference : $packageQueue->reference;

            // Group packages by HBL reference
            if (! isset($response[$hblReference])) {
                $response[$hblReference] = [
                    'hbl_reference' => $hblReference,
                    'customer' => $packageQueue->token->customer->name,
                    'packages' => [],
                    'total_packages' => 0,
                    'released_packages' => 0,
                ];
            }

            $packageData = [
                'id' => $packageQueue->id,
                'package_queue_id' => $packageQueue->id,
                'reference' => $packageQueue->reference,
                'package_count' => $packageQueue->package_count,
                'is_released' => $packageQueue->is_released,
                'released_at' => $packageQueue->released_at,
                'released_packages' => $packageQueue->released_packages,
                'released_by' => $packageQueue->releasedBy ? $packageQueue->releasedBy->name : null,
                'note' => $packageQueue->note,
                'created_at' => $packageQueue->created_at,
                'logs' => $packageQueue->releaseLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'type' => $log->type,
                        'packages' => $log->packages,
                        'remarks' => $log->remarks,
                        'created_at' => $log->created_at,
                        'created_by' => $log->createdBy ? $log->createdBy->name : null,
                    ];
                }),
            ];

            $response[$hblReference]['packages'][] = $packageData;
            $response[$hblReference]['total_packages'] += $packageQueue->package_count;
            if ($packageQueue->is_released) {
                $response[$hblReference]['released_packages'] += $packageQueue->package_count;
            }
        }

        return response()->json([
            'token_number' => $token,
            'customer' => $packageQueues->first()->token->customer->name,
            'hbl_groups' => array_values($response),
            'summary' => [
                'total_hbls' => count($response),
                'total_packages' => collect($response)->sum('total_packages'),
                'released_packages' => collect($response)->sum('released_packages'),
                'available_for_return' => collect($response)->sum('released_packages'),
            ],
        ]);
    }

    public function returnPackage(array $data): void
    {
        // Handle selective package returns
        if (isset($data['selected_packages']) && ! empty($data['selected_packages'])) {
            foreach ($data['selected_packages'] as $packageData) {
                $packageQueue = PackageQueue::find($packageData['package_queue_id']);

                if ($packageQueue && $packageQueue->is_released) {
                    // Store the released packages before clearing them
                    $releasedPackages = $packageQueue->released_packages ?? [];

                    // Create a release log entry for the return
                    PackageReleaseLog::create([
                        'package_queue_id' => $packageQueue->id,
                        'type' => 'return',
                        'packages' => [
                            [
                                'reference' => $packageQueue->reference,
                                'package_count' => $packageQueue->package_count,
                                'returned_at' => now()->toDateTimeString(),
                                'original_released_packages' => $releasedPackages,
                            ],
                        ],
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
            }

            return;
        }

        // Legacy single package return (maintain backward compatibility)
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
