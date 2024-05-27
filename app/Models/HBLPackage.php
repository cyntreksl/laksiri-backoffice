<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy(BranchScope::class)]
class HBLPackage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'hbl_packages';

    protected $fillable = [
        'hbl_id',
        'branch_id',
        'package_type',
        'length',
        'width',
        'height',
        'quantity',
        'volume',
        'weight',
        'remarks',
    ];
}
