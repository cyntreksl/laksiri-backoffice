<?php

namespace App\Services;

use App\Models\Token;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TokenCancellationService
{
    public function __construct(
        private TokenCancellationValidator $validator,
        private TokenCancellationReverser $reverser,
        private TokenCancellationAuditor $auditor
    ) {
    }

    /**
     * Cancel a token with all associated reversals and audit logging.
     *
     * @param Token $token
     * @param string $cancellationReason
     * @param User $cancelledBy
     * @return TokenCancellationResult
     */
    public function cancelToken(Token $token, string $cancellationReason, User $cancelledBy): TokenCancellationResult
    {
        // Validate eligibility before proceeding
        $validationResult = $this->validator->validate($token);
        
        if ($validationResult->isFailure()) {
            return new TokenCancellationResult(
                success: false,
                message: $validationResult->getMessage(),
                errorCode: $validationResult->getErrorCode()
            );
        }

        // Check HBL package locations and collect warnings
        $packageStatusData = $this->auditor->collectHBLPackageStatus($token);
        $warnings = $packageStatusData['warnings'];

        // Execute cancellation in transaction
        try {
            $auditLog = $this->executeInTransaction($token, $cancellationReason, $cancelledBy);

            return new TokenCancellationResult(
                success: true,
                message: 'Token cancelled successfully',
                tokenId: $token->id,
                cancelledAt: $token->cancelled_at,
                invoiceCancelled: $auditLog->invoice_cancelled,
                gatePassCancelled: $auditLog->gate_pass_cancelled,
                warnings: $warnings,
                auditLogId: $auditLog->id
            );
        } catch (\Exception $e) {
            Log::error('Token cancellation failed', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new TokenCancellationResult(
                success: false,
                message: 'Token cancellation failed: ' . $e->getMessage(),
                errorCode: 'CANCELLATION_FAILED'
            );
        }
    }

    /**
     * Execute all cancellation operations within a database transaction.
     *
     * @param Token $token
     * @param string $cancellationReason
     * @param User $cancelledBy
     * @return \App\Models\TokenCancellation
     * @throws \Exception
     */
    private function executeInTransaction(Token $token, string $cancellationReason, User $cancelledBy)
    {
        return DB::transaction(function () use ($token, $cancellationReason, $cancelledBy) {
            // Update token status and cancellation fields
            $token->is_cancelled = true;
            $token->cancelled_at = now();
            $token->cancelled_by = $cancelledBy->id;
            $token->cancellation_reason = $cancellationReason;
            $token->save();

            // Cancel invoice
            $invoiceCancelled = $this->reverser->cancelInvoice($token);
            if (!$invoiceCancelled) {
                throw new \Exception('Failed to cancel invoice');
            }

            // Cancel gate pass
            $gatePassCancelled = $this->reverser->cancelGatePass($token);
            if (!$gatePassCancelled) {
                throw new \Exception('Failed to cancel gate pass');
            }

            // Remove from queues
            $this->reverser->removeFromQueues($token);

            // Create audit log
            $auditLog = $this->auditor->createAuditLog(
                $token,
                $cancellationReason,
                $cancelledBy,
                $invoiceCancelled,
                $gatePassCancelled
            );

            return $auditLog;
        });
    }
}

/**
 * Value object representing the result of a token cancellation operation.
 */
class TokenCancellationResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?string $errorCode = null,
        public readonly ?int $tokenId = null,
        public readonly ?\DateTimeInterface $cancelledAt = null,
        public readonly ?bool $invoiceCancelled = null,
        public readonly ?bool $gatePassCancelled = null,
        public readonly array $warnings = [],
        public readonly ?int $auditLogId = null
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }
}
