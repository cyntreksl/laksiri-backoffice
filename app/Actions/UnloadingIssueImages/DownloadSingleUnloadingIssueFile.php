<?php

namespace App\Actions\UnloadingIssueImages;

use App\Models\UnloadingIssueFile;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadSingleUnloadingIssueFile
{
    use AsAction;

    public function handle(string $id)
    {
        $file = UnloadingIssueFile::findOrFail($id);

        return $file->getFirstMedia();
    }
}
