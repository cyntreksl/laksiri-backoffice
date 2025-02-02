<?php

namespace App\Repositories\CallCenter;

use App\Actions\ReceptionVerification\CreateReceptionVerification;
use App\Interfaces\CallCenter\ReceptionRepositoryInterface;
use App\Models\CustomerQueue;

class ReceptionRepository implements ReceptionRepositoryInterface
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
}
