<?php

namespace App\Actions\ContainerDocument;

use App\Models\ContainerDocument;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadDocument
{
    use AsAction;

    public function handle(ContainerDocument $container_document)
    {
        $filePath = $container_document->document;

        if (Storage::disk('s3')->exists($filePath)) {
            return Storage::disk('s3')->download($filePath);
        }
    }
}
