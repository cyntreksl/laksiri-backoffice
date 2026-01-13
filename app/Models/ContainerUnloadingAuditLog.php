<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContainerUnloadingAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'container_id',
        'action',
        'level',
        'hbl_package_id',
        'hbl_id',
        'mhbl_id',
        'hbl_number',
        'mhbl_number',
        'package_count',
        'package_details',
        'packages_affected',
        'performed_by',
        'notes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'package_details' => 'array',
        'packages_affected' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['description'];

    /**
     * Get the container that this audit log belongs to
     */
    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    /**
     * Get the HBL package (for package-level operations)
     */
    public function hblPackage(): BelongsTo
    {
        return $this->belongsTo(HBLPackage::class);
    }

    /**
     * Get the HBL (for HBL-level operations)
     */
    public function hbl(): BelongsTo
    {
        return $this->belongsTo(HBL::class);
    }

    /**
     * Get the MHBL (for MHBL-level operations)
     */
    public function mhbl(): BelongsTo
    {
        return $this->belongsTo(MHBL::class);
    }

    /**
     * Get the user who performed the action
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope to filter by action type
     */
    public function scopeUnloads($query)
    {
        return $query->where('action', 'unload');
    }

    /**
     * Scope to filter by reload action
     */
    public function scopeReloads($query)
    {
        return $query->where('action', 'reload');
    }

    /**
     * Scope to filter by level
     */
    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope to filter by container
     */
    public function scopeForContainer($query, int $containerId)
    {
        return $query->where('container_id', $containerId);
    }

    /**
     * Get a human-readable description of the action
     */
    public function getDescriptionAttribute(): string
    {
        $action = $this->action === 'unload' ? 'unloaded' : 'reloaded';
        $level = ucfirst($this->level);
        
        if ($this->level === 'package') {
            return "Package {$action} from container";
        } elseif ($this->level === 'hbl') {
            return "{$level} {$this->hbl_number} ({$this->package_count} packages) {$action}";
        } elseif ($this->level === 'mhbl') {
            return "{$level} {$this->mhbl_number} ({$this->package_count} packages) {$action}";
        }
        
        return "{$level} {$action}";
    }
}
