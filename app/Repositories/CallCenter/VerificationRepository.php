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

                // check payment status
                $payment = GetPaymentByReference::run($customerQueue->token->reference);

                // If $data is an object, convert it to an array
                $paymentArray = (array) $payment->getData();

                if (! empty($paymentArray)) {
                    if ($payment->getData()->paid_amount >= $payment->getData()->grand_total) {
                        // send examination queue
                        $customerQueue->create([
                            'type' => CustomerQueue::EXAMINATION_QUEUE,
                            'token_id' => $customerQueue->token_id,
                        ]);

                        // find token
                        $token = Token::find($data['customer_queue']['token_id']);

                        // find hbl
                        if ($token) {
                            $hbl = HBL::where('reference', $token->reference)->withoutGlobalScopes()->firstOrFail();

                            // create package queue
                            PackageQueue::create([
                                'token_id' => $customerQueue->token_id,
                                'hbl_id' => $hbl->id,
                                'auth_id' => auth()->id(),
                                'reference' => $token->reference,
                                'package_count' => $token->package_count,
                            ]);

                            // set queue status log
                            $customerQueue->addQueueStatus(
                                CustomerQueue::EXAMINATION_QUEUE,
                                $customerQueue->token->customer_id,
                                $customerQueue->token_id,
                                now(),
                                null,
                            );
                        }
                    } else {
                        // send to cashier queue
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
                } else {
                    // send to cashier queue
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
            'data' => VerifiedCollection::collection($records),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }

    public function getHBLPaymentsDetails(Token $token): JsonResponse
    {
        return GetPaymentByReference::run($token->reference);
    }
}
