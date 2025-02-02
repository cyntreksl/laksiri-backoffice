<?php

namespace App\Repositories\CallCenter;

use App\Actions\ReceptionVerification\CreateReceptionVerification;
use App\Http\Resources\CallCenter\ReceptionVerifiedCollection;
use App\Interfaces\CallCenter\ReceptionRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\CustomerQueue;

class ReceptionRepository implements GridJsInterface, ReceptionRepositoryInterface
{
    public function storeVerification(array $data): void
    {
        try {
            CreateReceptionVerification::run($data);

            $customerQueue = CustomerQueue::find($data['customer_queue']['id']);

            if ($customerQueue) {
                $customerQueue->update([
                    'left_at' => now(),
                ]);

                // set queue status log
                $customerQueue->addQueueStatus(
                    CustomerQueue::RECEPTION_VERIFICATION_QUEUE,
                    $customerQueue->token->customer_id,
                    $customerQueue->token_id,
                    null,
                    now(),
                );

                $customerQueue->create([
                    'type' => CustomerQueue::DOCUMENT_VERIFICATION_QUEUE,
                    'token_id' => $customerQueue->token_id,
                ]);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to reception verified: '.$e->getMessage());
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = CustomerQueue::query()
            ->receptionQueue()
            ->has('token.reception_verification');

        $records = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = $query->count();

        return response()->json([
            'data' => ReceptionVerifiedCollection::collection($records),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }
}
