<?php

namespace App\Actions\UnloadingIssueImages;

use App\Models\UnloadingIssueFile;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUnloadingIssueImages
{
    use AsAction;

    public function handle($unloadingIssue)
    {
        $unloadingIssueFile = UnloadingIssueFile::where('package_id', $unloadingIssue->hbl_package_id)->get();

        if (! $unloadingIssueFile) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return $unloadingIssueFile->map(function ($file) {
            return [
                'id' => $file->id,
                'name' => $file->getFirstMedia()->name,
                'url' => $file->getFirstMediaUrl(),
                'type' => $file->getFirstMedia()->mime_type,
                'size' => $file->getFirstMedia()->human_readable_size,
            ];
        });
        //        return response()->json([
        //            'status' => 'success',
        //            'images' => $unloadingIssueFile->map(function ($file) {
        //                return [
        //                    'id' => $file->id,
        //                    'name' => $file->getFirstMedia()->name,
        //                    'url' => $file->getFirstMediaUrl(),
        //                    'type' => $file->getFirstMedia()->mime_type,
        //                    'size' => $file->getFirstMedia()->human_readable_size,
        //                ];
        //            }),
        //        ]);
    }
}
