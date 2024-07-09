<?php

namespace App\Interfaces;

use App\Models\Container;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface LoadedContainerRepositoryInterface
{
    public function store(array $data);

    public function deleteDraft(array $data);

    public function downloadManifestFile(Container $container): BinaryFileResponse;
}
