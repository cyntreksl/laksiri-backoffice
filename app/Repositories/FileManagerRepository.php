<?php

namespace App\Repositories;

use App\Actions\FileManager\GetFilesWithProperties;
use App\Actions\FileManager\UploadFiles;
use App\Interfaces\FileManagerRepositoryInterface;

class FileManagerRepository implements FileManagerRepositoryInterface
{
    public function getAllFilesWithProperties()
    {
        return GetFilesWithProperties::run();
    }

    public function uploadFiles(array $data): void
    {
        try {
            UploadFiles::run($data);
        } catch (\Exception $exception) {
            throw new \Exception('Failed to upload file: '.$exception->getMessage());
        }
    }
}
