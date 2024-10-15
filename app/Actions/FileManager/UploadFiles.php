<?php

namespace App\Actions\FileManager;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\LaksiriFile;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadFiles
{
    use AsAction;

    public function handle(array $data)
    {
        foreach ($data as $file) {
            $laksiriFile = new LaksiriFile();
            $laksiriFile->branch_id = GetUserCurrentBranchID::run();
            $laksiriFile->name = $file->getClientOriginalName();
            $laksiriFile->save();
            $laksiriFile->addMedia($file)->toMediaCollection();
        }
    }
}
