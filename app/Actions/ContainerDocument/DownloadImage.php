<?php

namespace App\Actions\ContainerDocument;

use App\Models\UnloadingIssue;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadImage
{
    use AsAction;

    public function handle($id)
    {
        $UnloadingIssueFile = UnloadingIssue::find($id);

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
