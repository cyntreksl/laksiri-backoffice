<?php

namespace App\Actions\UnloadingIssueImages;

use App\Models\UnloadingIssueFile;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUnloadingIssueImages
{
    use AsAction;

    public function handle($unloadingIssue)
    {

        $unloadingIssueFile = UnloadingIssueFile::where('package_id', $unloadingIssue->hbl_package_id)->first();

        if (! $unloadingIssueFile) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $mediaPath = $unloadingIssueFile->getFirstMediaUrl();

        if (! $mediaPath) {
            return response()->json(['message' => 'Media not found'], 404);
        }

        $path = Storage::disk(config('filesystems.default'))->url($unloadingIssueFile->name);

        return response()->json([
            'status' => 'success',
            'image' => $path,
        ]);
    }
}
