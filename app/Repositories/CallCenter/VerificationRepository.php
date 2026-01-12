<?php

namespace App\Repositories\CallCenter;

use App\Actions\HBL\HBLPayment\GetPaymentByReference;
use App\Actions\Verification\CreateVerification;
use App\Http\Resources\CallCenter\VerifiedCollection;
use App\Interfaces\CallCenter\VerificationRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use App\Models\PackageQueue;
use App\Models\Token;
use Illuminate\Http\JsonResponse;

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

                // Always send to cashier queue (Step-by-step flow)
                $customerQueue->create([
                    'type' => CustomerQueue::CASHIER_QUEUE,
                    'token_id' => $customerQueue->token_id,
                ]);

                // set queue status log
                $customerQueue->addQueueStatus(
                    CustomerQueue::CASHIER_QUEUE,
                    $customerQueue->token->customer_id,
                    $customerQueue->token_id,
                    now(),
                    null,
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

        $records = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => VerifiedCollection::collection($records),
            'meta' => [
                'total' => $records->total(),
                'current_page' => $records->currentPage(),
                'perPage' => $records->perPage(),
                'lastPage' => $records->lastPage(),
            ],
        ]);
    }

    public function getHBLPaymentsDetails(Token $token): JsonResponse
    {
        return GetPaymentByReference::run($token->reference);
    }
}
