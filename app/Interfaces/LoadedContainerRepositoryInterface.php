<?php

namespace App\Interfaces;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface LoadedContainerRepositoryInterface
{
    public function store(array $data);

    public function deleteDraft(array $data);

    public function downloadManifestFile($container): BinaryFileResponse;
}
