<?php

namespace App\Interfaces;

interface LoadedContainerRepositoryInterface
{
    public function store(array $data);

    public function deleteDraft(array $data);

    public function downloadManifestFile($container);

    public function updateVerificationStatus(array $data);
}
