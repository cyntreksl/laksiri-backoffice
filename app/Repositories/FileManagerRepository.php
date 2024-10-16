<?php

namespace App\Repositories;

use App\Actions\FileManager\DeleteSingleFile;
use App\Actions\FileManager\DownloadSingleFile;
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

    public function downloadSingleFile(string $id)
    {
        try {
            return DownloadSingleFile::run($id);
        } catch (\Exception $exception) {
            throw new \Exception('Failed to download file: '.$exception->getMessage());
        }
    }

    public function deleteFile(string $id)
    {
        try {
            return DeleteSingleFile::run($id);
        } catch (\Exception $exception) {
            throw new \Exception('Failed to delete file: '.$exception->getMessage());
        }
    }
}
