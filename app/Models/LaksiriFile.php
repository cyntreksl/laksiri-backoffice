<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ScopedBy(BranchScope::class)]
class LaksiriFile extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'laksiri_files';
}
