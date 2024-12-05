<?php

namespace App\Interfaces;

interface AnyFileUploadRepositoryInterface
{
    public function anyFileUpload(array $data, int $id);
}
