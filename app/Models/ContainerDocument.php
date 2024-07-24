<?php

namespace App\Models;

use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContainerDocument extends Model
{
    use HasFactory;
    use HasFile;

    protected $table = 'container_documents';

    protected $fillable = [
        'container_id', 'uploaded_by', 'document_name', 'document',
    ];
}
