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

        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath);
        }
    }
}
