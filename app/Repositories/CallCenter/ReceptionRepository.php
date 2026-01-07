<?php

namespace App\Repositories\CallCenter;

use App\Actions\HBL\HBLPayment\GetPaymentByReference;
use App\Actions\ReceptionVerification\CreateReceptionVerification;
use App\Http\Resources\CallCenter\ReceptionVerifiedCollection;
use App\Interfaces\CallCenter\ReceptionRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use App\Models\PackageQueue;

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

                // Check if all documents are verified
                $receptionVerification = $customerQueue->token->reception_verification;
                $allDocumentsVerified = $receptionVerification && $receptionVerification->all_documents_verified;

                // Determine next queue based on verification and payment status
                if ($allDocumentsVerified) {
                    // Skip document verification queue - check payment status
                    $payment = GetPaymentByReference::run($customerQueue->token->reference);
                    $paymentData = (array) $payment->getData();

                    if (! empty($paymentData)) {
                        $paymentObj = $payment->getData();
                        $hbl = HBL::where('reference', $customerQueue->token->reference)->withoutGlobalScopes()->first();

                        if ($hbl && $hbl->paid_amount >= $paymentObj->grand_total) {
                            // Fully paid - go directly to examination queue
                            $customerQueue->create([
                                'type' => CustomerQueue::EXAMINATION_QUEUE,
                                'token_id' => $customerQueue->token_id,
                            ]);

                            // Create package queue
                            PackageQueue::create([
                                'token_id' => $customerQueue->token_id,
                                'hbl_id' => $hbl->id,
                                'auth_id' => auth()->id(),
                                'reference' => $customerQueue->token->reference,
                                'package_count' => $customerQueue->token->package_count,
                            ]);

                            // Set queue status log
                            $customerQueue->addQueueStatus(
                                CustomerQueue::EXAMINATION_QUEUE,
                                $customerQueue->token->customer_id,
                                $customerQueue->token_id,
                                now(),
                                null,
                            );
                        } else {
                            // Not paid - go to cashier queue
                            $customerQueue->create([
                                'type' => CustomerQueue::CASHIER_QUEUE,
                                'token_id' => $customerQueue->token_id,
                            ]);

                            // Set queue status log
                            $customerQueue->addQueueStatus(
                                CustomerQueue::CASHIER_QUEUE,
                                $customerQueue->token->customer_id,
                                $customerQueue->token_id,
                                now(),
                                null,
                            );
                        }
                    } else {
                        // No payment record - go to cashier queue
                        $customerQueue->create([
                            'type' => CustomerQueue::CASHIER_QUEUE,
                            'token_id' => $customerQueue->token_id,
                        ]);

                        // Set queue status log
                        $customerQueue->addQueueStatus(
                            CustomerQueue::CASHIER_QUEUE,
                            $customerQueue->token->customer_id,
                            $customerQueue->token_id,
                            now(),
                            null,
                        );
                    }
                } else {
                    // Documents not fully verified - go to document verification queue
                    $customerQueue->create([
                        'type' => CustomerQueue::DOCUMENT_VERIFICATION_QUEUE,
                        'token_id' => $customerQueue->token_id,
                    ]);
                }
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

        $records = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => ReceptionVerifiedCollection::collection($records),
            'meta' => [
                'total' => $records->total(),
                'current_page' => $records->currentPage(),
                'perPage' => $records->perPage(),
                'lastPage' => $records->lastPage(),
            ],
        ]);
    }
}
