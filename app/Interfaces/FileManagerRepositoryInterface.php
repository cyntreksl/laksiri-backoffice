<?php

namespace App\Interfaces;

interface FileManagerRepositoryInterface
{
    public function getAllFilesWithProperties();

    public function uploadFiles(array $data);
}
