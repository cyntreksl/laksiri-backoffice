<?php

namespace App\Interfaces;

use App\Models\Container;

interface ContainerRepositoryInterface
{
    public function store(array $data): Container;

    public function unloadHBLFromContainer(array $data, Container $container);

    public function batchHBLDownload(Container $container);
}
