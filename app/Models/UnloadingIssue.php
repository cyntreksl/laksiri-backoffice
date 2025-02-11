<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnloadingIssue extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'hbl_package_id', 'issue', 'type', 'is_damaged', 'rtf', 'is_fixed', 'note',
    ];

    public function hblPackage(): BelongsTo
    {
        return $this->belongsTo(HBLPackage::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(HBLPackage::class);
    }

    public function files()
    {
        return $this->hasOne(UnloadingIssueFile::class, 'package_id', 'hbl_package_id');
    }
}
