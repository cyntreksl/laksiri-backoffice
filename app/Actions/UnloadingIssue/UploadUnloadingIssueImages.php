<?php

namespace App\Actions\UnloadingIssue;

use App\Models\UnloadingIssueFile;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadUnloadingIssueImages
{
    use AsAction;

    public function handle(array $data)
    {
        foreach ($data['files'] as $file) {
            $unloadingIssueFile = new UnloadingIssueFile();
            $unloadingIssueFile->package_id = $data['hbl_package_id'];
            $unloadingIssueFile->name = $file->getClientOriginalName();
            $unloadingIssueFile->save();
            $unloadingIssueFile->addMedia($file)->toMediaCollection();
        }
    }
}
