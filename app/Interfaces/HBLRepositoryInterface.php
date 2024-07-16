<?php

namespace App\Interfaces;

use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLDocument;
use Illuminate\Http\JsonResponse;

interface HBLRepositoryInterface
{
    public function getHBLs();

    public function storeHBL(array $data);

    public function updateHBL(array $data, HBL $HBL);

    public function deleteHBL(HBL $HBL);

    public function getHBLsWithPackages();

    public function toggleHold(HBL $hbl);

    public function getUnloadedHBLsByCargoType(array $data);

    public function getLoadedHBLsByCargoType(Container $container, string $cargoType);

    public function getHBLWithUnloadedPackagesByReference(string $reference, string $cargo_type);

    public function downloadHBLPDF(HBL $hbl);

    public function downloadHBLInvoicePDF(HBL $hbl);

    public function downloadHBLBarcodePDF(HBL $hbl);

    public function getCancelledList(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse;

    public function restore($id);

    public function getHBLByPackageId($package_id);

    public function uploadDocument(array $data);

    public function deleteDocument(HBLDocument $hblDocument);

    public function getPickupStatus(HBL $hbl);
}
