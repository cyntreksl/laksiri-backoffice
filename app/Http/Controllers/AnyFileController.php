<?php

namespace App\Http\Controllers;

use App\Interfaces\FileManagerRepositoryInterface;
use Illuminate\Http\Request;

class AnyFileController extends Controller
{
    public function __construct(protected readonly FileManagerRepositoryInterface $fileUploadRepository)
    {
    }

    public function upload(Request $request, $id)
    {
        $request->validate([
            'files' => 'required |array',
            'files.*' => 'required |file|mimes:pdf,doc,docx,jpg,png,jpeg|max:2048',
        ]);

        try {
            $uploadFiles = $this->fileUploadRepository->anyFileUpload($request->all(), $id);

            return response()->json(['message' => 'Files uploaded successfully', 'files' => $uploadFiles], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'File upload failed', 'error' => $e->getMessage()], 500);
        }
    }
}
