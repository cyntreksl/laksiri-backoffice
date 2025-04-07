<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy(BranchScope::class)]
class AirLine extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'air_lines';

    protected $fillable = [
        'branch_id',
        'name',
        'created_by',
    ];
}
