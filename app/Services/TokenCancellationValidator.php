<?php

namespace App\Services;

use App\Models\Token;

class TokenCancellationValidator
{
    /**
     * Validate if a token can be cancelled.
     *
     * @param Token $token
     * @return ValidationResult
     */
    public function validate(Token $token): ValidationResult
    {
        // Check if token is already cancelled
        if ($token->isCancelled()) {
            return new ValidationResult(
                success: false,
                message: 'Token is already cancelled',
                errorCode: 'ALREADY_CANCELLED'
            );
        }

        // Check if token is completed and outside 3-day window
        if ($token->isCompleted() && !$this->canCancelCompletedToken($token)) {
            return new ValidationResult(
                success: false,
                message: 'Completed tokens can only be cancelled within 3 days of issue',
                errorCode: 'OUTSIDE_CANCELLATION_WINDOW'
            );
        }

        // Token can be cancelled
        return new ValidationResult(
            success: true,
            message: 'Token is eligible for cancellation'
        );
    }

    /**
     * Check if a completed token is within the 3-day cancellation window.
     *
     * @param Token $token
     * @return bool
     */
    public function canCancelCompletedToken(Token $token): bool
    {
        // diffInDays() returns the number of complete 24-hour periods
        // So a token issued 3 days ago (72 hours) will return 3
        // We want to allow cancellation for 0, 1, 2, and 3 days ago
        $daysSinceIssue = $token->created_at->diffInDays(now());
        return $daysSinceIssue < 4;
    }
}

/**
 * Value object representing the result of a validation check.
 */
class ValidationResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?string $errorCode = null
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
}
