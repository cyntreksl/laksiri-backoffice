<?php

namespace App\Actions\UnloadingIssue;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\UnloadingIssueFile;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadImages
{
    use AsAction;

    public function handle(array $data)
    {
        foreach ($data['files'] as $file) {
            $unloadingIssueFile = new UnloadingIssueFile();
            //            $laksiriFile->branch_id = GetUserCurrentBranchID::run();
            //            $laksiriFile->name = $file->getClientOriginalName();
            //            $laksiriFile->save();
            //            $laksiriFile->addMedia($file)->toMediaCollection();
        }
    }
}
