<?php

namespace App\Interfaces;

interface LoadedContainerRepositoryInterface
{
    public function store(array $data);

    public function deleteDraft(string $hblPackageId);
}
