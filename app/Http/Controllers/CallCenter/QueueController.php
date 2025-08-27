<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\QueueRepositoryInterface;
use App\Models\PackageQueue;
use App\Models\PackageReleaseLog;
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
        $packageQueue = PackageQueue::whereHas('token', function ($query) use ($token) {
            $query->where('token', $token);
        })->with(['token.customer', 'releasedBy'])->first();

        if (!$packageQueue) {
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

    public function returnPackage(Request $request)
    {
        $request->validate([
            'token_number' => 'required|string|exists:package_queues,token_id',
            'remarks' => 'nullable|string',
        ]);

        // Find the package queue by token number
        $packageQueue = PackageQueue::whereHas('token', function ($query) use ($request) {
            $query->where('token', $request->token_number);
        })->first();

        if (!$packageQueue) {
            return response()->json(['error' => 'Package not found'], 404);
        }


        // Create a release log entry for the return
        PackageReleaseLog::create([
            'package_queue_id' => $packageQueue->id,
            'type' => 'return',
            'packages' => $packageQueue->hbl_packages ?? [],
            'remarks' => $request->remarks,
            'created_by' => auth()->id(),
        ]);

        // Update the package queue to mark as not released
        $packageQueue->update([
            'is_released' => false,
            'released_at' => null,
            'released_packages' => null,
            'note' => $request->remarks,
            'auth_id' => auth()->id(),
        ]);

        return response()->json(['message' => 'Package returned successfully']);
    }
}
