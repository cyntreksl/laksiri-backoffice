<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class UnloadingIssueFile extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'unloading_issue_files';

    protected $fillable = ['package_id', 'name'];
}
