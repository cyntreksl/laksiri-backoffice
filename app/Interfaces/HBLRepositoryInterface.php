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

    public function downloadHBLPDF(HBL $HBL);

    public function downloadHBLInvoicePDF(HBL $hbl);

    public function downloadHBLBarcodePDF(HBL $hbl);

    public function getCancelledList(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse;

    public function restore($id);

    public function getHBLByPackageId($package_id);

    public function uploadDocument(array $data);

    public function deleteDocument(HBLDocument $hblDocument);

    public function getPickupStatus($id): JsonResponse;

    public function getHBLStatus(HBL $HBL): JsonResponse;

    public function export(array $filters);

    public function exportCancelled(array $filters);

    public function getHBLByReference(string $reference): JsonResponse;

    public function getHBLPackagesByReference(string $reference): JsonResponse;

    public function getHBLStatusByReference(string $reference): JsonResponse;

    public function downloadDocument(HBLDocument $hbl_document);

    public function calculatePayment(array $data): JsonResponse;

    public function getDraftList(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse;

    public function createCallFlag(HBL $hbl, array $data);

    public function getHBLPackageRules(array $data);

    public function getHBLRules(array $data);

    public function downloadGatePass($hbl);

    public function getDoorToDoorHBL(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []);

    public function downloadBaggagePDF(HBL $hbl);

    public function getDestinationUnloadedHBLsByCargoType(array $data);
}
