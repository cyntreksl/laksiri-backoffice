<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLaksiriFileRequest;
use App\Interfaces\FileManagerRepositoryInterface;
use Inertia\Inertia;

class FileController extends Controller
{
    public function __construct(protected readonly FileManagerRepositoryInterface $fileManagerRepository) {}

    public function index()
    {
        return Inertia::render('FileManager/Index', [
            'files' => $this->fileManagerRepository->getAllFilesWithProperties(),
        ]);
    }

    public function upload(StoreLaksiriFileRequest $request)
    {
        $this->fileManagerRepository->uploadFiles($request->all());
    }

    public function download($id)
    {
        return $this->fileManagerRepository->downloadSingleFile($id);
    }

    public function destroy($id)
    {
        $this->fileManagerRepository->deleteFile($id);
    }
}
