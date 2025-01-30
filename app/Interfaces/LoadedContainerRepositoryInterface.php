<?php

namespace App\Interfaces;

use App\Models\Container;

interface LoadedContainerRepositoryInterface
{
    public function store(array $data);

    public function deleteDraft(array $data);

    public function downloadManifestFile($container);

    public function updateVerificationStatus(array $data);

    public function downloadDoorToDoorPdf($container);

    public function downloadUnloadingPointDoc($container);
}
