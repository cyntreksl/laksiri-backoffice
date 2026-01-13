<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierHBLPayment extends Model
{
    use HasFactory;

    protected $table = 'cashier_hbl_payments';

    protected $fillable = [
        'verified_by', 'customer_queue_id', 'token_id', 'hbl_id', 'paid_amount', 'note', 'invoice_number', 'receipt_number', 'verified_at',
    ];

    protected static function booted(): void
    {
        static::creating(function ($payment) {
            // Generate invoice number if not set
            if (empty($payment->invoice_number)) {
                $payment->invoice_number = self::generateInvoiceNumber();
            }
            
            // Generate receipt number if not set
            if (empty($payment->receipt_number)) {
                $payment->receipt_number = self::generateReceiptNumber();
            }
        });
    }

    /**
     * Generate unique invoice number with format: INV-YYYY-XXXXX
     */
    private static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";
        
        // Get the last invoice number for this year
        $lastInvoice = self::where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            // Extract sequence number from last invoice
            $lastSequence = (int) substr($lastInvoice->invoice_number, -5);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        return $prefix . str_pad($newSequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique receipt number with format: RCP-YYYY-XXXXX
     */
    private static function generateReceiptNumber(): string
    {
        $year = date('Y');
        $prefix = "RCP-{$year}-";
        
        // Get the last receipt number for this year
        $lastReceipt = self::where('receipt_number', 'like', "{$prefix}%")
            ->orderBy('receipt_number', 'desc')
            ->first();
        
        if ($lastReceipt) {
            // Extract sequence number from last receipt
            $lastSequence = (int) substr($lastReceipt->receipt_number, -5);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        return $prefix . str_pad($newSequence, 5, '0', STR_PAD_LEFT);
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class, 'token_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
