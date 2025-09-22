<?php

namespace App\Interfaces;

interface LoadedContainerRepositoryInterface
{
    public function store(array $data);

    public function deleteDraft(array $data);

    public function downloadManifestFile($container);

    public function updateVerificationStatus(array $data);

    public function downloadDoorToDoorPdf($container);

    public function downloadUnloadingPointDoc($container);

    public function getLoadedContainer(string $id);

    public function loadMHBL(array $data);

    public function tallySheetDownloadPDF($container);

    public function tallySheetDownloadExcel($container);

    public function downloadManifestExcel($container);
}
