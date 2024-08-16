<?php

namespace App\Repositories\CallCenter;

use App\Actions\Verification\CreateVerification;
use App\Interfaces\CallCenter\VerificationRepositoryInterface;
use App\Models\CustomerQueue;

class VerificationRepository implements VerificationRepositoryInterface
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
}
