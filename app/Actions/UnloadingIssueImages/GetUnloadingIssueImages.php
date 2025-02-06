<?php

namespace App\Actions\UnloadingIssueImages;

use App\Models\UnloadingIssue;
use App\Models\UnloadingIssueFile;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUnloadingIssueImages
{
    use AsAction;

    public function handle($unloadingIssue)
    {
        $unloadingIssueFile = UnloadingIssueFile::where('package_id',$unloadingIssue->hbl_package_id)->get();

        if (! $UnloadingIssueFile) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $mediaPath = $UnloadingIssueFile->files->getFirstMediaUrl();
        $path = Storage::disk(config('filesystems.default'))->url($mediaPath);
        //        $mediaPath = Storage::disk(config('filesystems.default'))->url($UnloadingIssueFile->files->getFirstMediaPath());
        if (! $mediaPath) {
            return response()->json(['message' => 'Media not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'image' => $path,
        ]);
    }
}
