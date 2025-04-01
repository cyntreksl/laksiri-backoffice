<?php

namespace App\Models;

use App\Enum\HBLImageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HBLImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'officer_id',
        'hbl_id',
        'hbl_packages_id',
        'image_type',
        'image_path',
    ];

    protected $table = 'hbl_images';

    protected $casts = [
        'image_type' => HBLImageType::class,
    ];

    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function hbl()
    {
        return $this->belongsTo(HBL::class);
    }

    public function hblPackage()
    {
        return $this->belongsTo(HBLPackage::class, 'hbl_packages_id');
    }
}
