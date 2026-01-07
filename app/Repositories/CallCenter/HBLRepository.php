<?php

namespace App\Repositories\CallCenter;

use App\Actions\HBL\GetHBLs;
use App\Actions\HBL\GetHBLsWithPackages;
use App\Actions\HBL\HBLPayment\GetPaymentByReference;
use App\Enum\HBLType;
use App\Factory\HBL\FilterFactory;
use App\Http\Resources\CallCenter\HBLDeliverResource;
use App\Http\Resources\CallCenter\HBLResource;
use App\Interfaces\CallCenter\HBLRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use App\Models\PackageQueue;
use App\Models\Scopes\BranchScope;
use App\Models\Token;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class HBLRepository implements GridJsInterface, HBLRepositoryInterface
{
    public function getHBLs()
    {
        return GetHBLs::run();
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query()
            ->withoutGlobalScope(BranchScope::class)
            ->with('tokens.customerQueue')
            ->withCount('callFlags')
            ->with(['callFlags' => function ($query) {
                $query->orderBy('date', 'desc');
            }])
            ->with('containers');

        if (! empty($search)) {
            $query->whereAny([
                'reference',
                'hbl_number',
                'hbl_name',
                'contact_number',
                'consignee_name',
                'consignee_nic',
                'consignee_contact',
                'iq_number',
                'nic',
                'email',
            ], 'like', '%'.$search.'%');
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $hbls = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => HBLResource::collection($hbls),
            'meta' => [
                'total' => $hbls->total(),
                'current_page' => $hbls->currentPage(),
                'perPage' => $hbls->perPage(),
                'lastPage' => $hbls->lastPage(),
            ],
        ]);
    }

    public function createAndIssueToken(HBL $hbl)
    {
        // create token
        if ($hbl->consignee_id) {
            // Get the current date
            $today = Carbon::today();

            // Get the last token created today
            $lastToken = Token::whereDate('created_at', $today)->orderBy('id', 'desc')->first();

            // Determine the token value
            $tokenValue = $lastToken ? $lastToken->token + 1 : 1;

            $token = Token::create([
                'hbl_id' => $hbl->id,
                'customer_id' => $hbl->consignee_id,
                'receptionist_id' => auth()->id(),
                'reference' => $hbl->reference,
                'package_count' => $hbl->packages->count(),
                'token' => $tokenValue,
            ]);

            // set customer queue
            $token->customerQueue()->create([
                'type' => CustomerQueue::DOCUMENT_VERIFICATION_QUEUE,
            ]);

            // set queue status log
            $hbl->addQueueStatus(
                CustomerQueue::DOCUMENT_VERIFICATION_QUEUE,
                $hbl->consignee_id,
                $token->id,
                date('Y-m-d H:i:s', (time() - 60)),
                now(),
            );

            // print token pdf
            $customPaper = [0, 0, 283.80, 567.00];

            $pdf = Pdf::loadView('pdf.customer.token', [
                'token' => $token->load(['hbl' => function ($query) {
                    $query->withoutGlobalScope(BranchScope::class);
                }]),
            ])->setPaper($customPaper);

            $filename = $hbl->hbl_number.'.pdf';

            return $pdf->download($filename);
        }
    }

    public function createAndIssueTokenWithVerification(HBL $hbl, array $verificationData)
    {
        // create token
        if ($hbl->consignee_id) {
            // Get the current date
            $today = Carbon::today();

            // Get the last token created today
            $lastToken = Token::whereDate('created_at', $today)->orderBy('id', 'desc')->first();

            // Determine the token value
            $tokenValue = $lastToken ? $lastToken->token + 1 : 1;

            $token = Token::create([
                'hbl_id' => $hbl->id,
                'customer_id' => $hbl->consignee_id,
                'receptionist_id' => auth()->id(),
                'reference' => $hbl->reference,
                'package_count' => $hbl->packages->count(),
                'token' => $tokenValue,
            ]);

            // Determine the required documents based on HBL type
            $requiredDocs = [];
            if ($hbl->hbl_type === 'UPB') {
                $requiredDocs = ['Passport', 'HBL Receipt'];
            } else {
                $requiredDocs = ['NIC', 'HBL Receipt'];
            }

            // Check if all required documents are verified
            $checkedDocs = $verificationData['is_checked'] ?? [];
            $allDocumentsVerified = count($requiredDocs) > 0 &&
                                  count($checkedDocs) > 0 &&
                                  collect($requiredDocs)->every(function ($doc) use ($checkedDocs) {
                                      return isset($checkedDocs[$doc]) && $checkedDocs[$doc] === true;
                                  });

            // Determine next queue based on verification and payment status
            $queueResult = $this->determineNextQueue($hbl, $token, $allDocumentsVerified);

            $customerQueue = $token->customerQueue()->create([
                'type' => $queueResult['queue_type'],
            ]);

            // Store the reception verification data
            $customerQueue->reception_verification()->create([
                'is_checked' => $verificationData['is_checked'] ?? [],
                'note' => $verificationData['note'] ?? null,
                'verified_by' => auth()->id(),
                'token_id' => $token->id,
                'all_documents_verified' => $allDocumentsVerified,
            ]);

            // If skipping to examination queue, create package queue
            if ($queueResult['queue_type'] === CustomerQueue::EXAMINATION_QUEUE) {
                PackageQueue::create([
                    'token_id' => $token->id,
                    'hbl_id' => $hbl->id,
                    'auth_id' => auth()->id(),
                    'reference' => $token->reference,
                    'package_count' => $token->package_count,
                ]);
            }

            // set queue status log
            $hbl->addQueueStatus(
                $queueResult['queue_type'],
                $hbl->consignee_id,
                $token->id,
                date('Y-m-d H:i:s', (time() - 60)),
                now(),
            );

            // print token pdf
            $customPaper = [0, 0, 283.80, 567.00];

            $pdf = Pdf::loadView('pdf.customer.token', [
                'token' => $token->load(['hbl' => function ($query) {
                    $query->withoutGlobalScope(BranchScope::class);
                }]),
            ])->setPaper($customPaper);

            $filename = $hbl->hbl_number.'.pdf';
            $pdfPath = storage_path('app/public/tokens/'.$filename);

            // Return JSON response with token data
            return response()->json([
                'success' => true,
                'message' => 'Token issued successfully',
                'token' => [
                    'id' => $token->id,
                    'token_number' => $token->token,
                    'reference' => $token->reference,
                    'queue_type' => $queueResult['queue_type'],
                    'all_documents_verified' => $allDocumentsVerified,
                    'is_paid' => $queueResult['is_paid'],
                    'skipped_document_verification' => $queueResult['skipped_document_verification'],
                    'skipped_cashier' => $queueResult['skipped_cashier'],
                ],
            ]);
        }

        return response()->json(['success' => false, 'message' => 'HBL has no consignee'], 400);
    }

    /**
     * Determine the next queue based on verification and payment status
     *
     * @param HBL $hbl
     * @param Token $token
     * @param bool $allDocumentsVerified
     * @return array
     */
    private function determineNextQueue(HBL $hbl, Token $token, bool $allDocumentsVerified): array
    {
        $skippedDocumentVerification = false;
        $skippedCashier = false;
        $isPaid = false;

        // If all documents verified at reception, skip document verification queue
        if ($allDocumentsVerified) {
            $skippedDocumentVerification = true;

            // Check payment status
            $payment = GetPaymentByReference::run($token->reference);
            $paymentData = (array) $payment->getData();

            if (! empty($paymentData)) {
                $paymentObj = $payment->getData();
                $isPaid = $hbl->paid_amount >= $paymentObj->grand_total;

                if ($isPaid) {
                    // Fully paid - go directly to examination queue
                    $skippedCashier = true;
                    return [
                        'queue_type' => CustomerQueue::EXAMINATION_QUEUE,
                        'is_paid' => true,
                        'skipped_document_verification' => true,
                        'skipped_cashier' => true,
                    ];
                }
            }

            // Not paid - go to cashier queue
            return [
                'queue_type' => CustomerQueue::CASHIER_QUEUE,
                'is_paid' => false,
                'skipped_document_verification' => true,
                'skipped_cashier' => false,
            ];
        }

        // Documents not verified - go to reception verification queue
        return [
            'queue_type' => CustomerQueue::RECEPTION_VERIFICATION_QUEUE,
            'is_paid' => false,
            'skipped_document_verification' => false,
            'skipped_cashier' => false,
        ];
    }

    public function generateTokenPDF($tokenId, $type = 'download')
    {
        $token = Token::with(['hbl' => function ($query) {
            $query->withoutGlobalScope(BranchScope::class);
        }])->findOrFail($tokenId);

        // 4 inch width = 288 points (72 points per inch)
        // Initial height of 400 points (will expand as needed)
        $customPaper = [0, 0, 288, 400]; // 4 inches wide, expandable height

        $pdf = Pdf::loadView('pdf.customer.token', [
            'token' => $token,
        ])->setPaper($customPaper);

        $filename = $token->hbl->hbl_number.'_token.pdf';

        if ($type === 'download') {
            return $pdf->download($filename);
        } else {
            // For print, we'll return inline for browser printing
            return $pdf->stream($filename);
        }
    }

    public function getHBLsWithPackages()
    {
        return GetHBLsWithPackages::run();
    }

    public function getDoorToDoorHBL(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query()
            ->where('hbl_type', '=', HBLType::DOOR_TO_DOOR->value)
            ->where('system_status', '>=', 4.3)
            ->where('is_released', '=', 0)
            ->where(function ($query) {
                $query->where('status', '=', 'reached')
                    ->orWhereNull('status');
            });

        if (! empty($search)) {
            $query->whereAny([
                'reference',
                'hbl_number',
                'hbl_name',
                'contact_number',
                'consignee_name',
                'consignee_nic',
                'consignee_contact',
                'iq_number',
                'nic',
                'email',
            ], 'like', '%'.$search.'%');
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $hbls = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => HBLDeliverResource::collection($hbls),
            'meta' => [
                'total' => $hbls->total(),
                'current_page' => $hbls->currentPage(),
                'perPage' => $hbls->perPage(),
                'lastPage' => $hbls->lastPage(),
            ],
        ]);
    }
}
