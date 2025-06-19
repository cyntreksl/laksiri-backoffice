<?php

namespace App\Actions\ThirdPartyShipment;

use App\Models\TmpHbl;
use App\Models\TmpHblPackage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class ImportHblFromCsv
{
    use AsAction;

    public function handle(UploadedFile $file): array
    {
        $sessionId = Str::uuid();
        $csvData = $this->parseCsv($file);

        $groupedData = [];
        $errors = [];

        foreach ($csvData as $index => $row) {
            $rowNumber = $index + 2; // Account for header row

            try {
                $hblNumber = trim($row['HBL NUMBER'] ?? '');

                if (empty($hblNumber)) {
                    $errors[] = "Row {$rowNumber}: HBL NUMBER is required";

                    continue;
                }

                // Group by HBL number
                if (! isset($groupedData[$hblNumber])) {
                    $groupedData[$hblNumber] = [
                        'hbl' => $this->extractHblData($row, $sessionId),
                        'packages' => [],
                    ];
                }

                // Add package data
                $packageData = $this->extractPackageData($row, $sessionId, $hblNumber);
                if ($packageData) {
                    $groupedData[$hblNumber]['packages'][] = $packageData;
                }

            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: ".$e->getMessage();
            }
        }

        if (! empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
            ];
        }

        // Save to temporary tables
        $savedHbls = [];
        foreach ($groupedData as $hblNumber => $data) {
            $hbl = TmpHbl::create($data['hbl']);

            foreach ($data['packages'] as $packageData) {
                TmpHblPackage::create($packageData);
            }

            $savedHbls[] = $hbl;
        }

        return [
            'success' => true,
            'session_id' => $sessionId,
            'hbls_count' => count($savedHbls),
            'total_packages' => array_sum(array_map(fn ($data) => count($data['packages']), $groupedData)),
        ];
    }

    private function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $data = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($header)) {
                $data[] = array_combine($header, $row);
            }
        }

        fclose($handle);

        return $data;
    }

    private function extractHblData(array $row, string $sessionId): array
    {
        return [
            'session_id' => $sessionId,
            'hbl_number' => trim($row['HBL NUMBER'] ?? ''),
            'hbl_name' => trim($row['SHIPPER NAME'] ?? ''),
            'email' => trim($row['SHIPPER EMAIL'] ?? ''),
            'contact_number' => trim($row['SHIPPER MOBILE'] ?? ''),
            'additional_mobile_number' => trim($row['SHIPPER ADDITIONAL CONTACT'] ?? ''),
            'whatsapp_number' => trim($row['SHIPPER WHATSAPP'] ?? ''),
            'nic' => trim($row['SHIPPER NIC'] ?? ''),
            'iq_number' => trim($row['SHIPPER RECIDENCY NO'] ?? ''),
            'address' => trim($row['SHIPPER ADDRESS'] ?? ''),
            'consignee_name' => trim($row['CONSIGNEE NAME'] ?? ''),
            'consignee_nic' => trim($row['CONSIGNEE NIC'] ?? ''),
            'consignee_contact' => trim($row['CONSIGNEE MOBILE'] ?? ''),
            'consignee_additional_mobile_number' => trim($row['CONSIGNEE ADDITIONAL CONTACT'] ?? ''),
            'consignee_whatsapp_number' => trim($row['CONSIGNEE WHATSAPP'] ?? ''),
            'consignee_address' => trim($row['CONSIGNEE ADDRESS'] ?? ''),
            'consignee_note' => trim($row['CONSIGNEE NOTE'] ?? ''),
        ];
    }

    private function extractPackageData(array $row, string $sessionId, string $hblNumber): ?array
    {
        $packageType = trim($row['PACKAGE TYPE'] ?? '');
        $originalLength = (float) ($row['LENGTH'] ?? 0);
        $originalWidth = (float) ($row['WIDTH'] ?? 0);
        $originalHeight = (float) ($row['HEIGHT'] ?? 0);
        $quantity = (int) ($row['QUANTITY'] ?? 0);
        $measureType = strtolower(trim($row['MEASURE TYPE'] ?? 'cm'));

        // Skip if essential package data is missing
        if (empty($packageType) || $originalLength <= 0 || $originalWidth <= 0 || $originalHeight <= 0 || $quantity <= 0) {
            return null;
        }

        // Convert all dimensions to centimeters
        $length = $this->convertToCentimeters($originalLength, $measureType);
        $width = $this->convertToCentimeters($originalWidth, $measureType);
        $height = $this->convertToCentimeters($originalHeight, $measureType);

        // Auto-calculate volume in cubic centimeters, then convert to cubic meters
        $volumeInCm3 = $length * $width * $height * $quantity;
        $volumeInM3 = $volumeInCm3 / 1000000; // Convert cm³ to m³

        return [
            'session_id' => $sessionId,
            'hbl_number' => $hblNumber,
            'package_type' => $packageType,
            'measure_type' => 'cm', // Store as cm since everything is converted
            'length' => round($length, 2),
            'width' => round($width, 2),
            'height' => round($height, 2),
            'quantity' => $quantity,
            'volume' => round($volumeInM3, 6), // Volume in m³
            'weight' => (float) ($row['TOTAL WEIGHT'] ?? 0),
            'remarks' => trim($row['REMARKS'] ?? ''),
        ];
    }

    /**
     * Convert dimensions to centimeters based on the measure type
     */
    private function convertToCentimeters(float $value, string $measureType): float
    {
        switch ($measureType) {
            case 'inch':
            case 'inches':
            case 'in':
                return $value * 2.54; // inches to cm

            case 'meter':
            case 'meters':
            case 'm':
                return $value * 100; // meters to cm

            case 'foot':
            case 'feet':
            case 'ft':
                return $value * 30.48; // feet to cm

            case 'millimeter':
            case 'millimeters':
            case 'mm':
                return $value / 10; // mm to cm

            case 'centimeter':
            case 'centimeters':
            case 'cm':
            default:
                return $value; // already in cm
        }
    }
}
