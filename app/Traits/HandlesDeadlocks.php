<?php

namespace App\Traits;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HandlesDeadlocks
{
    /**
     * Execute a transaction with deadlock retry logic
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function transactionWithDeadlockRetry(callable $callback, int $maxRetries = 3, int $baseDelay = 100)
    {
        return $this->executeWithDeadlockRetry(function () use ($callback) {
            return DB::transaction($callback);
        }, $maxRetries, $baseDelay);
    }

    /**
     * Execute a callback with deadlock retry logic
     *
     * @param  int  $baseDelay  Base delay in milliseconds
     * @return mixed
     *
     * @throws \Exception
     */
    protected function executeWithDeadlockRetry(callable $callback, int $maxRetries = 3, int $baseDelay = 100)
    {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return $callback();
            } catch (QueryException $e) {
                $attempt++;

                // Check if this is a deadlock error (MySQL error code 1213)
                if ($this->isDeadlockError($e) && $attempt < $maxRetries) {
                    // Log the deadlock attempt
                    Log::warning("Deadlock detected, attempt {$attempt}/{$maxRetries}", [
                        'error' => $e->getMessage(),
                        'sql' => $e->getSql(),
                        'bindings' => $e->getBindings(),
                    ]);

                    // Rollback any active transaction
                    if (DB::transactionLevel() > 0) {
                        DB::rollBack();
                    }

                    // Calculate exponential backoff delay
                    $delay = $baseDelay * pow(2, $attempt - 1);

                    // Add some jitter to prevent thundering herd
                    $jitter = rand(0, $delay / 2);
                    $totalDelay = $delay + $jitter;

                    // Sleep for the calculated delay (convert to microseconds)
                    usleep($totalDelay * 1000);

                    continue;
                }

                // If it's not a deadlock or we've exceeded max retries, re-throw
                throw $e;
            } catch (\Exception $e) {
                // For non-database exceptions, rollback and re-throw immediately
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
                throw $e;
            }
        }

        // This should never be reached, but just in case
        throw new \Exception('Maximum deadlock retry attempts exceeded');
    }

    /**
     * Check if the exception is a deadlock error
     */
    protected function isDeadlockError(QueryException $e): bool
    {
        // MySQL deadlock error code is 1213
        // PostgreSQL deadlock error code is 40001
        return in_array($e->getCode(), [1213, '40001']) ||
               str_contains($e->getMessage(), 'Deadlock found') ||
               str_contains($e->getMessage(), 'deadlock detected');
    }
}
