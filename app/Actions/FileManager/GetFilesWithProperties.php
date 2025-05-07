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
            $media = $file->getFirstMedia();

            return [
                'id' => $file->id,
                'name' => $media?->name ?? 'No file',
                'url' => $media ? $file->getFirstMediaUrl() : null,
                'type' => $media?->mime_type ?? null,
                'size' => $media?->human_readable_size ?? null,
            ];
        });
    }
}
