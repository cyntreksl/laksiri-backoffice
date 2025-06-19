<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TmpHblPackage extends Model
{
    use HasFactory;

    protected $table = 'tmp_hbl_packages';

    protected $fillable = [
        'session_id',
        'hbl_number',
        'package_type',
        'measure_type',
        'length',
        'width',
        'height',
        'quantity',
        'volume',
        'weight',
        'remarks',
    ];

    protected $casts = [
        'length' => 'double',
        'width' => 'double',
        'height' => 'double',
        'quantity' => 'integer',
        'volume' => 'double',
        'weight' => 'double',
    ];

    public function hbl()
    {
        return $this->belongsTo(TmpHbl::class, 'hbl_number', 'hbl_number')
            ->where('session_id', $this->session_id);
    }
}
