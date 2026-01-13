<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelTokenRequest;
use App\Models\Token;
use App\Services\TokenCancellationAuditor;
use App\Services\TokenCancellationService;
use App\Services\TokenCancellationValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TokenCancellationController extends Controller
{
    public function __construct(
        private readonly TokenCancellationService $cancellationService,
        private readonly TokenCancellationValidator $validator,
        private readonly TokenCancellationAuditor $auditor
    ) {
    }

    /**
     * Cancel a token with the provided cancellation reason.
     *
     * @param CancelTokenRequest $request
     * @param Token $token
     * @return JsonResponse
     */
    public function cancel(CancelTokenRequest $request, Token $token): JsonResponse
    {
        try {
            // Get the authenticated user
            $user = $request->user();

            // Call the service to cancel the token
            $result = $this->cancellationService->cancelToken(
                $token,
                $request->input('cancellation_reason'),
                $user
            );

            // Check if cancellation was successful
            if ($result->isSuccess()) {
                return response()->json([
                    'success' => true,
                    'message' => $result->getMessage(),
                    'data' => [
                        'token_id' => $result->tokenId,
                        'cancelled_at' => $result->cancelledAt?->toIso8601String(),
                        'invoice_cancelled' => $result->invoiceCancelled,
                        'gate_pass_cancelled' => $result->gatePassCancelled,
                        'warnings' => $result->getWarnings(),
                    ],
                ]);
            }

            // Handle validation or business logic errors
            $statusCode = match ($result->getErrorCode()) {
                'ALREADY_CANCELLED', 'OUTSIDE_CANCELLATION_WINDOW' => 400,
                'CANCELLATION_FAILED' => 500,
                default => 422,
            };

            return response()->json([
                'success' => false,
                'message' => $result->getMessage(),
                'error_code' => $result->getErrorCode(),
            ], $statusCode);

        } catch (\Exception $e) {
            Log::error('Token cancellation controller error', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while cancelling the token',
                'error_code' => 'UNEXPECTED_ERROR',
            ], 500);
        }
    }

    /**
     * Check if a token is eligible for cancellation and return warnings.
     *
     * @param Token $token
     * @return JsonResponse
     */
    public function checkEligibility(Token $token): JsonResponse
    {
        try {
            // Validate eligibility
            $validationResult = $this->validator->validate($token);

            // Collect HBL package status and warnings
            $packageStatusData = $this->auditor->collectHBLPackageStatus($token);

            // Calculate days since issue
            $daysSinceIssue = $token->created_at->diffInDays(now());

            return response()->json([
                'eligible' => $validationResult->isSuccess(),
                'reason' => $validationResult->getMessage(),
                'warnings' => $packageStatusData['warnings'],
                'token_status' => $token->isCompleted() ? 'completed' : 'in_progress',
                'issued_date' => $token->created_at->toIso8601String(),
                'days_since_issue' => $daysSinceIssue,
            ]);

        } catch (\Exception $e) {
            Log::error('Token eligibility check error', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'eligible' => false,
                'reason' => 'An error occurred while checking eligibility',
                'warnings' => [],
                'token_status' => 'unknown',
                'issued_date' => null,
                'days_since_issue' => null,
            ], 500);
        }
    }
}
