<?php

namespace App\Repositories\CallCenter;

use App\Actions\HBL\DownloadAllBaggageReceipts;
use App\Actions\HBL\GetHBLs;
use App\Actions\HBL\GetHBLsWithPackages;
use App\Actions\HBL\HBLPayment\GetPaymentByReference;
use App\Actions\Setting\GetSettings;
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
use Wnx\SidecarBrowsershot\BrowsershotLambda;

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
            ->with(['packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class)
                    ->with(['containers' => function ($cQuery) {
                        $cQuery->withoutGlobalScope(BranchScope::class);
                    }]);
            }]);

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
        // Handle token issuance demurrage consent if provided
        if (isset($verificationData['demurrage_consent_given']) && $verificationData['demurrage_consent_given'] === true) {
            $hbl->token_demurrage_consent_given = true;
            $hbl->token_demurrage_consent_by = auth()->id();
            $hbl->token_demurrage_consent_at = now();
            $hbl->token_demurrage_consent_note = $verificationData['demurrage_consent_note'] ?? 'Token issued without container reached date';
            $hbl->save();
        }
        
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
            $queueResult = $this->determineNextQueue();

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
    private function determineNextQueue(): array
    {
        // Enforce step-by-step flow: Always go to Document Verification Queue first
        // Remove skip conditions for document verification and cashier
        
        return [
            'queue_type' => CustomerQueue::DOCUMENT_VERIFICATION_QUEUE,
            'is_paid' => false, // We force them to go through the flow regardless of payment status
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

    public function generateAllBaggageReceipts($container)
    {
        return DownloadAllBaggageReceipts::run($container);
    }

    public function streamAllBaggageReceipts($container)
    {
        // Get HBL IDs from the container's packages
        $hblIds = $container->hbl_packages()->pluck('hbl_id')->unique();

        // Get HBLs with their packages and containers
        $hbls = HBL::whereIn('id', $hblIds)
            ->with(['packages', 'containers'])
            ->get();

        if ($hbls->isEmpty()) {
            abort(404, 'No HBLs found for this container');
        }

        $settings = GetSettings::run();
        $logoPath = asset('images/app-logo.png') ?? null;

        $template = view('exports.baggage-bulk', [
            'hbls' => $hbls,
            'container' => $container,
            'settings' => $settings,
            'logoPath' => $logoPath,
        ])->render();

        $filename = 'baggage-receipts-'.$container->reference.'.pdf';
        $filePath = storage_path("app/public/{$filename}");

        BrowsershotLambda::html($template)
            ->showBackground()
            ->format('A4')
            ->save($filePath);

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"'
        ])->deleteFileAfterSend(true);
    }

    public function generateBaggageReceiptsZip($container)
    {
        // Get HBL IDs from the container's packages
        $hblIds = $container->hbl_packages()->pluck('hbl_id')->unique();

        // Get HBLs with their packages and containers
        $hbls = HBL::whereIn('id', $hblIds)
            ->with(['packages', 'containers'])
            ->get();

        if ($hbls->isEmpty()) {
            abort(404, 'No HBLs found for this container');
        }

        $settings = GetSettings::run();
        $logoPath = asset('images/app-logo.png') ?? null;

        $zip = new \ZipArchive();
        $zipFileName = 'baggage-receipts-'.$container->reference.'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        // Ensure temp directory exists
        if (! file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($hbls as $hbl) {
                $template = view('exports.baggage', [
                    'hbl' => $hbl,
                    'containers' => $hbl->containers->first(),
                    'settings' => $settings,
                    'logoPath' => $logoPath,
                ])->render();

                $pdfFileName = 'baggage-receipt-'.$hbl->hbl_number.'.pdf';
                $pdfPath = storage_path('app/temp/'.$pdfFileName);

                BrowsershotLambda::html($template)
                    ->showBackground()
                    ->format('A4')
                    ->save($pdfPath);

                $zip->addFile($pdfPath, $pdfFileName);
            }

            $zip->close();

            // Clean up individual PDF files
            foreach ($hbls as $hbl) {
                $pdfFileName = 'baggage-receipt-'.$hbl->hbl_number.'.pdf';
                $pdfPath = storage_path('app/temp/'.$pdfFileName);
                if (file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
            }

            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }

        abort(500, 'Failed to create ZIP file');
    }
}
