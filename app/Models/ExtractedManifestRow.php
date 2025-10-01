<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtractedManifestRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'file_name',
        'row_number',
        'row_type',
        'raw_data',
        'extracted_text',
        'obl_number',
        'vessel_name',
        'shipper_info',
        'consignee_info',
        'container_number',
        'container_type',
        'hbl_number',
        'hbl_name',
        'hbl_contact',
        'hbl_address',
        'package_type',
        'package_quantity',
        'package_weight',
        'package_volume',
        'package_description',
        'is_processed',
        'processing_status',
        'processing_notes',
        'validation_errors',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'validation_errors' => 'array',
        'is_processed' => 'boolean',
    ];

    /**
     * Scope to get rows by session ID
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope to get rows by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('row_type', $type);
    }

    /**
     * Scope to get unprocessed rows
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('is_processed', false);
    }

    /**
     * Scope to get processed rows
     */
    public function scopeProcessed($query)
    {
        return $query->where('is_processed', true);
    }

    /**
     * Scope to get rows with errors
     */
    public function scopeWithErrors($query)
    {
        return $query->where('processing_status', 'error');
    }

    /**
     * Get header rows for a session
     */
    public function scopeHeaders($query, $sessionId)
    {
        return $query->bySession($sessionId)->byType('header');
    }

    /**
     * Get container rows for a session
     */
    public function scopeContainers($query, $sessionId)
    {
        return $query->bySession($sessionId)->byType('container');
    }

    /**
     * Get HBL rows for a session
     */
    public function scopeHbls($query, $sessionId)
    {
        return $query->bySession($sessionId)->byType('hbl');
    }

    /**
     * Get package rows for a session
     */
    public function scopePackages($query, $sessionId)
    {
        return $query->bySession($sessionId)->byType('package');
    }

    /**
     * Get consignee rows for a session
     */
    public function scopeConsignees($query, $sessionId)
    {
        return $query->bySession($sessionId)->byType('consignee');
    }

    /**
     * Mark this row as processed
     */
    public function markAsProcessed($notes = null)
    {
        $this->update([
            'is_processed' => true,
            'processing_status' => 'processed',
            'processing_notes' => $notes,
        ]);
    }

    /**
     * Mark this row as having errors
     */
    public function markAsError($errors, $notes = null)
    {
        $this->update([
            'processing_status' => 'error',
            'validation_errors' => is_array($errors) ? $errors : [$errors],
            'processing_notes' => $notes,
        ]);
    }

    /**
     * Mark this row as skipped
     */
    public function markAsSkipped($reason = null)
    {
        $this->update([
            'processing_status' => 'skipped',
            'processing_notes' => $reason,
        ]);
    }

    /**
     * Get summary statistics for a session
     */
    public static function getSessionSummary($sessionId)
    {
        return [
            'total' => self::bySession($sessionId)->count(),
            'processed' => self::bySession($sessionId)->processed()->count(),
            'pending' => self::bySession($sessionId)->unprocessed()->count(),
            'errors' => self::bySession($sessionId)->withErrors()->count(),
            'by_type' => self::bySession($sessionId)
                ->select('row_type')
                ->selectRaw('count(*) as count')
                ->groupBy('row_type')
                ->pluck('count', 'row_type')
                ->toArray(),
        ];
    }
}