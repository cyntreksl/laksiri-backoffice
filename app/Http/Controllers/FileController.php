<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLaksiriFileRequest;
use App\Interfaces\FileManagerRepositoryInterface;
use Inertia\Inertia;

class FileController extends Controller
{
    public function __construct(protected readonly FileManagerRepositoryInterface $fileManagerRepository)
    {
    }

    public function index()
    {
        return Inertia::render('FileManager/Layout', [
            'files' => $this->fileManagerRepository->getAllFilesWithProperties(),
        ]);
    }

    public function upload(StoreLaksiriFileRequest $request)
    {
        $this->fileManagerRepository->uploadFiles($request->all());
    }
}
