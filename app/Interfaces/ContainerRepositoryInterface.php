<?php

namespace App\Interfaces;

use App\Models\Container;

interface ContainerRepositoryInterface
{
    public function store(array $data): Container;

    public function update(array $data, Container $container);

    public function unloadHBLFromContainer(array $data, Container $container);

    public function batchHBLDownload(Container $container);

    public function deleteLoading(Container $container);

    public function getLoadedContainers();

    public function unloadContainer(array $data);

    public function reloadContainer(array $data);

    public function createUnloadingIssue(array $data);

    public function markAsReached($containerId);

    public function export(array $filters);
}
