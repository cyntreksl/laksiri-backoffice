<?php

namespace App\Interfaces;

use App\Models\MHBL;
use Illuminate\Http\JsonResponse;

interface MHBLRepositoryInterface
{
    public function storeHBL(array $data);

    public function deleteMHBL(MHBL $MHBL);

    public function addNewHBL(array $data): JsonResponse;

    public function updateMHBL(MHBL $mhbl, array $data);

    public function getUnloadedMHBLs(array $data);

    public function getContainerLoadedMHBLs(array $data);

    public function getUnloadedMHBLHBL(string $reference);

    public function hblListDownloadPDF(MHBL $MHBL);
}
