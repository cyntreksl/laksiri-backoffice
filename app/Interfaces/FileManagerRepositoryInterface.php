<?php

namespace App\Interfaces;

interface FileManagerRepositoryInterface
{
    public function getAllFilesWithProperties();

    public function uploadFiles(array $data);

    public function downloadSingleFile(string $id);

    public function deleteFile(string $id);
}
