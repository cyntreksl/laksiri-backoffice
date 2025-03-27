<?php

namespace App\Models;

use App\Enum\HBLImageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HBLImage extends  Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hbl_id',
        'hbl_packages_id',
        'image_type',
        'image_path',
        'shipper_nic',
        'shipper_passport',
        'package_images'
    ];

    protected $table = 'hbl_images';


    protected $casts = [
        'package_images' => 'array',
        'image_type' => HBLImageType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
