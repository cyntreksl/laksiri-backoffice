<?php

namespace App\Repositories\CallCenter;

use App\Actions\Verification\CreateVerification;
use App\Http\Resources\CallCenter\CustomerQueueResource;
use App\Interfaces\CallCenter\VerificationRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\CustomerQueue;

class VerificationRepository implements GridJsInterface, VerificationRepositoryInterface
{
    public function storeVerification(array $data): void
    {
        try {
            CreateVerification::run($data);

            $customerQueue = CustomerQueue::find($data['customer_queue']['id']);

            if ($customerQueue) {
                $customerQueue->update([
                    'left_at' => now(),
                ]);

                // set queue status log
                $customerQueue->addQueueStatus(
                    CustomerQueue::DOCUMENT_VERIFICATION_QUEUE,
                    $customerQueue->token->customer_id,
                    $customerQueue->token_id,
                    null,
                    now(),
                );
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to verified: '.$e->getMessage());
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = CustomerQueue::query()
            ->documentVerificationQueue()
            ->has('token.verification');

        $records = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = $query->count();

        return response()->json([
            'data' => CustomerQueueResource::collection($records),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }
}
