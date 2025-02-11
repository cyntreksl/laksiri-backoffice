<?php

namespace App\Actions\UnloadingIssueImages;

use App\Models\UnloadingIssueFile;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteUnloadingIssueFile
{
    use AsAction;

    public function handle(UnloadingIssueFile $unloadingIssueFile)
    {
        $unloadingIssueFile->delete();
    }
}
