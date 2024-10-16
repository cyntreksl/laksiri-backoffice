<?php

namespace App\Actions\FileManager;

use App\Models\LaksiriFile;
use Lorisleiva\Actions\Concerns\AsAction;

class GetFilesWithProperties
{
    use AsAction;

    public function handle()
    {
        return LaksiriFile::orderBy('id', 'desc')->get()->map(function ($file) {
            return [
                'id' => $file->id,
                'name' => $file->getFirstMedia()->name,
                'url' => $file->getFirstMediaUrl(),
                'type' => $file->getFirstMedia()->mime_type,
                'size' => $file->getFirstMedia()->human_readable_size,
            ];
        });
    }
}
