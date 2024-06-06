<?php

namespace App\Interfaces;

use App\Models\Container;

interface LoadedContainerRepositoryInterface
{
    public function store(array $data);

    public function deleteDraft(string $hblPackageId);

    public function downloadManifestFile(Container $container);

    public function getLoadedContainers();
}
