<?php

namespace App\Actions\FileManager;

use App\Models\LaksiriFile;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadSingleFile
{
    use AsAction;

    public function handle(string $id)
    {
        $file = LaksiriFile::findOrFail($id);

        return $file->getFirstMedia();
    }
}
