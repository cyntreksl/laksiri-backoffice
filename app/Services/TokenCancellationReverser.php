<?php

namespace App\Services;

use App\Models\CustomerQueue;
use App\Models\Examination;
use App\Models\HBLPayment;
use App\Models\QueueLog;
use App\Models\SLInvoice;
use App\Models\Token;
use Illuminate\Support\Facades\Log;

class TokenCancellationReverser
{
    /**
     * Cancel the invoice associated with the token.
     *
     * @param Token $token
     * @return bool True if invoice was cancelled or no invoice exists, false on failure
     */
    public function cancelInvoice(Token $token): bool
    {
        try {
            // Find the HBL associated with the token
            $hbl = $token->hbl;
            
            if (!$hbl) {
                // No HBL means no invoice to cancel
                return true;
            }

            // Find the latest HBL payment (invoice) for this HBL
            $invoice = HBLPayment::where('hbl_id', $hbl->id)
                ->latest()
                ->first();

            if (!$invoice) {
                // No invoice exists, nothing to cancel
                return true;
            }

            // Mark the invoice as cancelled by soft deleting it
            $invoice->delete();

            Log::info('Invoice cancelled for token', [
                'token_id' => $token->id,
                'invoice_id' => $invoice->id,
                'hbl_id' => $hbl->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to cancel invoice', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Cancel the gate pass associated with the token.
     *
     * @param Token $token
     * @return bool True if gate pass was cancelled or no gate pass exists, false on failure
     */
    public function cancelGatePass(Token $token): bool
    {
        try {
            // Find the examination record (gate pass) for this token
            $examination = Examination::where('token_id', $token->id)
                ->where('is_issued_gate_pass', true)
                ->first();

            if (!$examination) {
                // No gate pass exists, nothing to cancel
                return true;
            }

            // Mark the gate pass as cancelled by setting is_issued_gate_pass to false
            $examination->is_issued_gate_pass = false;
            $examination->save();

            Log::info('Gate pass cancelled for token', [
                'token_id' => $token->id,
                'examination_id' => $examination->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to cancel gate pass', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Remove the token from all queues.
     *
     * @param Token $token
     * @return void
     * @throws \Exception
     */
    public function removeFromQueues(Token $token): void
    {
        try {
            // Remove from CustomerQueue table
            CustomerQueue::where('token_id', $token->id)->delete();

            // Remove from QueueLog table
            QueueLog::where('token_id', $token->id)->delete();

            Log::info('Token removed from all queues', [
                'token_id' => $token->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove token from queues', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
