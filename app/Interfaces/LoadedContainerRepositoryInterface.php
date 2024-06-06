<?php

namespace App\Interfaces;

use App\Models\Container;

interface LoadedContainerRepositoryInterface
{
    public function store(array $data);

    public function deleteDraft(array $data);

    public function downloadManifestFile(Container $container);

    public function getLoadedContainers();
}
