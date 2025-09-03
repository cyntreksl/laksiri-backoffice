<?php

namespace App\Exports;

use App\Actions\Branch\GetBranchByName;
use App\Actions\MHBL\GetMHBLById;
use App\Enum\WarehouseType;
use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class LoadedContainerManifestExcelExport implements FromCollection, ShouldAutoSize, WithStyles, WithEvents, WithCustomStartCell
{
    use Exportable;

    private Container $container;
    private ?array $processedData = null;
    private ?int $giftCount = null;
    private ?int $upbCount = null;
    private ?int $d2dCount = null;
    private ?string $cargoType = null;
    private $settings;
    private $branch;

    public function __construct(Container $container, $settings = null, $branch = null)
    {
        $this->container = $container;
        $this->settings = $settings;
        $this->branch = $branch;
    }

    public function setProcessedData(array $data, int $giftCount, int $upbCount, int $d2dCount, string $cargoType): void
    {
        $this->processedData = $data;
        $this->giftCount = $giftCount;
        $this->upbCount = $upbCount;
        $this->d2dCount = $d2dCount;
        $this->cargoType = $cargoType;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $this->styleSheet($event->sheet);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Styles are applied dynamically in the styleSheet method
        return [];
    }

    private function styleSheet($sheet)
    {
        $worksheet = $sheet->getDelegate();

        // Row 1: OBL, Company Name, Shipment Number
        $worksheet->setCellValue('A1', 'OBL');
        $worksheet->setCellValue('B1', $this->container->bl_number ?? 'ONEYDOHF00020500');
        $worksheet->mergeCells('C1:H1');
        $worksheet->setCellValue('C1', 'UNIVERSAL FREIGHT SERVICES');
        $worksheet->mergeCells('I1:K1');
        $worksheet->setCellValue('I1', 'SHIPMENT NO ' . ($this->container->reference ?? '2745'));
        $worksheet->getStyle('A1:K1')->getFont()->setBold(true);
        $worksheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('I1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $worksheet->getStyle('A1:K1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');

        // Row 2: CARGO MANIFEST
        $worksheet->mergeCells('A2:K2');
        $worksheet->setCellValue('A2', 'CARGO MANIFEST');
        $worksheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $worksheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');

        // Row 3: Vessel and voyage details
        $worksheet->setCellValue('A3', 'VESSEL :');
        $worksheet->setCellValue('B3', $this->container->vessel_name ?? 'YM COSMOS');
        $worksheet->setCellValue('C3', 'DATE LOADED :');
        $dateLoaded = $this->container->loading_started_at ? \Carbon\Carbon::parse($this->container->loading_started_at)->format('d.m.Y') : '09.01.2025';
        $worksheet->setCellValue('D3', $dateLoaded);
        $worksheet->setCellValue('H3', 'VOYAGE:');
        $worksheet->setCellValue('I3', $this->container->voyage_number ?? '181E');
        $worksheet->setCellValue('J3', 'ETA:');
        $etaDate = $this->container->estimated_time_of_arrival ? \Carbon\Carbon::parse($this->container->estimated_time_of_arrival)->format('d.m.Y') : '03.02.2025';
        $worksheet->setCellValue('K3', $etaDate);

        // Row 4: Shipper (Static as per image)
        $worksheet->setCellValue('A4', 'SHIPPER :');
        $worksheet->mergeCells('B4:K4');
        $worksheet->setCellValue('B4', 'UNIVERSAL FREIGHT SERVICES, P.O.BOX: 55239, DOHA, QATAR. TEL: +974 4620961 TEL/FAX: +974 4620812');

        // Row 5: Consignee (Static as per image)
        $worksheet->setCellValue('A5', 'CONSIGNEE :');
        $worksheet->mergeCells('B5:K5');
        $worksheet->setCellValue('B5', 'LAKSIRI SEVA (PVT) LTD. NO: 66, NEW NUGE ROAD, PELIYAGODA, SRI LANKA');

        // Row 6: Notify (Static as per image)
        $worksheet->setCellValue('A6', 'NOTIFY :');
        $worksheet->mergeCells('B6:K6');
        $worksheet->setCellValue('B6', 'LAKSIRI SEVA (PVT) LTD. NO: 31, ST.ANTHONY\'S MAWATHA, COLOMBO - 03, SRI LANKA. TEL : +94 11-2574180 / 11-47722800');

        // Row 7: Container details
        $worksheet->setCellValue('A7', 'CONTR NO :');
        $worksheet->setCellValue('B7', $this->container->container_number ?? 'TCLU1650570');
        $worksheet->setCellValue('D7', 'SEAL NO:');
        $worksheet->setCellValue('E7', $this->container->seal_number ?? 'QA013906A');
        $worksheet->setCellValue('H7', 'CONTAINER TYPE :');
        $worksheet->setCellValue('I7', '01 X ' . ($this->container->container_type ?? '40\' H/C'));

        // Calculate totals
        $data = $this->processedData ?? $this->prepareData();
        $total_nototal = 0;
        $total_vtotal = 0;
        $total_gtotal = 0;

        if (!empty($this->container->shipment_weight) && $this->container->shipment_weight > 0) {
            $total_gtotal = $this->container->shipment_weight;
        } else {
            foreach ($data as $item) {
                if (isset($item[9]) && is_object($item[9])) {
                    foreach ($item[9] as $package) {
                        $total_gtotal += $package['actual_weight'] ?? 0;
                    }
                }
            }
        }

        foreach ($data as $item) {
            if (isset($item[9]) && is_object($item[9])) {
                foreach ($item[9] as $package) {
                    $total_nototal += $package['quantity'] ?? 0;
                    $total_vtotal += $package['volume'] ?? 0;
                }
            }
        }

        // Row 8: Totals
        $worksheet->setCellValue('A8', 'NO OF PKG:');
        $worksheet->setCellValue('B8', number_format($total_nototal, 0));
        $worksheet->setCellValue('D8', 'TOTAL VOLUME :');
        $worksheet->setCellValue('E8', number_format($total_vtotal, 3));
        $worksheet->setCellValue('H8', 'TOTAL WEIGHT: KG');
        // Format weight with comma separator and no decimals to match image
        $worksheet->setCellValue('I8', number_format($total_gtotal, 0, '.', ','));

        // Row 9: Table headers
        $headers = ['SR NO', 'HBL', 'NAME OF SHIPPER', 'NAME OF CONSIGNEES', 'TYPE OF PKGS', 'NO.OF PKGS', 'VOLUME CBM', 'GWHT', 'DESCRIPTION OF CARGO', 'DELIVERY', 'REMARKS'];
        $worksheet->fromArray($headers, NULL, 'A9');
        $worksheet->getStyle('A9:K9')->getFont()->setBold(true)->setSize(10);
        $worksheet->getStyle('A9:K9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A9:K9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');

        // Data rows starting from row 10
        $currentRow = 10;
        $serialNumber = 1;
        $overallTotalVolume = $total_vtotal;
        $overallTotalWeight = $total_gtotal;

        foreach ($data as $item) {
            $startRow = $currentRow;

            // Prepare data for rows
            $shipperLines = array_filter(explode("\n", ($item[1] ?? '') . "\n" . ($item[2] ?? '') . "\n" . ($item[4] ?? '')));
            $consigneeLines = array_filter(explode("\n", ($item[5] ?? '') . "\n" . ($item[6] ?? '') . "\n" . ($item[8] ?? '') . "\n" . ($item[7] ?? '')));
            $packages = isset($item[9]) && is_object($item[9]) ? $item[9]->values() : collect(); // Ensure it's a zero-indexed array

            // Calculate totals for this HBL
            $hblTotalQuantity = $packages->sum('quantity');
            $hblTotalVolume = $packages->sum('volume');
            $hblWeight = ($overallTotalVolume > 0) ? ($overallTotalWeight / $overallTotalVolume) * $hblTotalVolume : 0;

            // Determine how many rows this HBL block needs
            $dataRowCount = max(count($shipperLines), count($consigneeLines), $packages->count(), 1); // Minimum 1 data row
            $totalBlockRows = $dataRowCount + 1; // +1 for the total row

            // Merge Cells Vertically
            $worksheet->mergeCells('A' . $startRow . ':A' . ($startRow + $totalBlockRows - 1));
            $worksheet->mergeCells('B' . $startRow . ':B' . ($startRow + $dataRowCount - 1));
            $worksheet->mergeCells('I' . $startRow . ':I' . ($startRow + $totalBlockRows - 1));
            $worksheet->mergeCells('J' . $startRow . ':J' . ($startRow + $totalBlockRows - 1));

            // Remarks logic
            $remarks = '';
            if (!empty($item[15]) && !empty($item[16])) { // is_departure_charges_paid && is_destination_charges_paid
                $branchCode = $this->branch['branchCode'] ?? 'DOH';
                $deliveryCode = $item[13] ?? 'CMB';
                $remarks = "{$branchCode} & {$deliveryCode} PAID";
            }
            $worksheet->mergeCells('K' . $startRow . ':K' . ($startRow + $dataRowCount - 1));
            $worksheet->setCellValue('K' . $startRow, $remarks);

            // Set values for merged cells
            $worksheet->setCellValue('A' . $startRow, $serialNumber++);
            $worksheet->setCellValue('B' . $startRow, $item[0] ?? ''); // HBL
            $worksheet->setCellValue('I' . $startRow, 'PERSONAL EFFECTS');
            $worksheet->setCellValue('J' . $startRow, $item[13] ?? ''); // Delivery

            // Set alignment for merged cells
            $worksheet->getStyle('A' . $startRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $worksheet->getStyle('B' . $startRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $worksheet->getStyle('I' . $startRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $worksheet->getStyle('J' . $startRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $worksheet->getStyle('K' . $startRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

            // Fill data rows
            for ($i = 0; $i < $dataRowCount; $i++) {
                $row = $startRow + $i;
                $worksheet->setCellValue('C' . $row, array_values($shipperLines)[$i] ?? '');
                $worksheet->setCellValue('D' . $row, array_values($consigneeLines)[$i] ?? '');

                if (isset($packages[$i])) {
                    $worksheet->setCellValue('E' . $row, $packages[$i]['package_type'] ?? '');
                    $worksheet->setCellValue('F' . $row, $packages[$i]['quantity'] ?? '');
                    $worksheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            }

            // Total row for this HBL
            $totalRow = $startRow + $dataRowCount;
            $worksheet->setCellValue('C' . $totalRow, 'PP NO:');
            $worksheet->setCellValue('D' . $totalRow, $item[3] ?? ''); // Shipper PP/NIC
            $worksheet->setCellValue('E' . $totalRow, 'TOTAL');
            $worksheet->setCellValue('F' . $totalRow, $hblTotalQuantity);
            $worksheet->setCellValue('G' . $totalRow, number_format($hblTotalVolume, 3));
            $worksheet->setCellValue('H' . $totalRow, number_format($hblWeight, 2));

            // Style the total row
            $totalRowRange = 'E' . $totalRow . ':H' . $totalRow;
            $worksheet->getStyle($totalRowRange)->getFont()->setBold(true);
            $worksheet->getStyle($totalRowRange)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
            $worksheet->getStyle('F' . $totalRow . ':H' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Move to the next block
            $currentRow = $totalRow + 1;
        }

        // Apply borders to the entire table
        $lastDataRow = $currentRow - 1;
        if ($lastDataRow >= 9) {
            $worksheet->getStyle('A9:K' . $lastDataRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }
        // Apply borders to header rows
        $worksheet->getStyle('A1:K8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Set column widths
        $worksheet->getColumnDimension('A')->setWidth(6);  // SR NO
        $worksheet->getColumnDimension('B')->setWidth(15); // HBL
        $worksheet->getColumnDimension('C')->setWidth(30); // SHIPPER
        $worksheet->getColumnDimension('D')->setWidth(30); // CONSIGNEE
        $worksheet->getColumnDimension('E')->setWidth(12); // TYPE OF PKGS
        $worksheet->getColumnDimension('F')->setWidth(10); // NO.OF PKGS
        $worksheet->getColumnDimension('G')->setWidth(12); // VOLUME CBM
        $worksheet->getColumnDimension('H')->setWidth(12); // GWHT
        $worksheet->getColumnDimension('I')->setWidth(18); // DESCRIPTION
        $worksheet->getColumnDimension('J')->setWidth(10); // DELIVERY
        $worksheet->getColumnDimension('K')->setWidth(20); // REMARKS
    }

    public function collection()
    {
        // Return empty collection since we're handling everything in the styling events
        return collect([]);
    }

    public function prepareData(): array
    {
        $data = [];
        $loadedMHBLPackages = [];
        $loadedHBLPackages = [];

        // Load relationships to prevent null pointer exceptions
        $this->container->load(['hbl_packages', 'duplicate_hbl_packages']);

        // Get the currently loaded HBL package IDs
        $currentlyLoadedPackageIds = $this->container->hbl_packages ? $this->container->hbl_packages->pluck('id')->toArray() : [];

        // Process duplicate_hbl_packages
        if ($this->container->duplicate_hbl_packages) {
            foreach ($this->container->duplicate_hbl_packages->groupBy('hbl_id') as $hblId => $packages) {
                $stillLoadedPackages = $packages->filter(function ($package) use ($currentlyLoadedPackageIds) {
                    return in_array($package->id, $currentlyLoadedPackageIds);
                });

                if ($stillLoadedPackages->isEmpty()) {
                    continue;
                }

                $hbl = HBL::withoutGlobalScope(BranchScope::class)->with('mhbl')->find($hblId);
                if ($hbl && $hbl->mhbl) {
                    $loadedMHBLPackages[$hbl->mhbl->id][] = $stillLoadedPackages;
                } else if ($hbl) {
                    $loadedHBLPackages[$hblId] = [
                        'hbl' => $hbl,
                        'packages' => $stillLoadedPackages,
                    ];
                }
            }
        }

        // Process MHBL packages
        foreach ($loadedMHBLPackages as $mhblId => $mhblPackage) {
            try {
                $mhbl = GetMHBLById::run($mhblId);
                $hblPackages = [];
                if (!empty($mhbl->hbls)) {
                    foreach ($mhbl->hbls as $mhblHBL) {
                        foreach ($mhblHBL->packages as $hblPackage) {
                            $hblPackages[] = $hblPackage;
                        }
                    }
                }

                $warehouse = $this->getWarehouseCode($mhbl->hbls[0] ?? null);

                $data[] = [
                    $mhbl->hbl_number ?: $mhbl->reference, // 0
                    $mhbl->shipper->name ?? '', // 1
                    $mhbl->shipper->address ?? '', // 2
                    $mhbl->shipper->pp_or_nic_no ?? '', // 3
                    $mhbl->shipper->mobile_number ?? '', // 4
                    $mhbl->consignee->name ?? '', // 5
                    $mhbl->consignee->address ?? '', // 6
                    $mhbl->consignee->pp_or_nic_no ?? '', // 7
                    $mhbl->consignee->mobile_number ?? '', // 8
                    collect($hblPackages ?? []), // 9
                    $mhbl->hbls[0]->paid_amount > 0 ? 'PAID' : 'UNPAID', // 10
                    'Gift', // 11
                    '', // 12
                    $warehouse, // 13
                    '', // 14
                    1, // 15
                    0, // 16
                    null, // 17
                    null, // 18
                    null, // 19
                ];
            } catch (\Exception $e) {
                Log::error('Error processing MHBL package: ' . $e->getMessage());
                continue;
            }
        }

        // Process HBL packages
        foreach ($loadedHBLPackages as $hblData) {
            try {
                $hbl = $hblData['hbl'];
                $warehouse = $this->getWarehouseCode($hbl);

                $isHBLFullLoad = $hbl->packages ? $hbl->packages->every(fn ($package) => $package->duplicate_containers->isNotEmpty()) : false;
                $hblLoadedContainers = $hbl->packages
                    ? $hbl->packages
                        ->load('duplicate_containers')
                        ->pluck('duplicate_containers')
                        ->flatten()
                        ->unique('id')
                        ->sortByDesc('created_at')
                    : collect();
                $hblLoadedLatestContainer = $hblLoadedContainers->first();

                $status = '';
                if ($isHBLFullLoad && count($hblLoadedContainers) === 1) {
                    $status = '';
                } elseif (count($hblLoadedContainers) > 1 && $hblLoadedLatestContainer && $hblLoadedLatestContainer['id'] === $this->container['id']) {
                    $status = 'BALANCE';
                } else {
                    $status = 'SHORT LOADED';
                }

                $loadedContainerReferences = $hbl->packages
                    ? $hbl->packages->load('duplicate_containers')
                        ->pluck('duplicate_containers')
                        ->flatten()
                        ->pluck('reference')
                        ->unique()
                    : collect();

                $filteredReferences = $loadedContainerReferences->reject(function ($ref) {
                    return $ref == $this->container['reference'];
                });
                $referencesString = $filteredReferences->implode(',');

                $data[] = [
                    $hbl->hbl_number ?: $hbl->reference, // 0
                    $hbl->hbl_name, // 1
                    $hbl->address, // 2
                    $hbl->nic, // 3
                    $hbl->contact_number, // 4
                    $hbl->consignee_name, // 5
                    $hbl->consignee_address, // 6
                    $hbl->consignee_nic, // 7
                    $hbl->consignee_contact . ($hbl->consignee_additional_mobile_number ? '/' . $hbl->consignee_additional_mobile_number : ''), // 8
                    $loadedHBLPackages[$hbl->id]['packages'], // 9
                    $hbl->paid_amount > 0 ? 'PAID' : 'UNPAID', // 10
                    $hbl->hbl_type, // 11
                    $hbl->other_charge, // 12
                    $warehouse, // 13
                    $hbl->iq_number, // 14
                    $hbl->is_departure_charges_paid, // 15
                    $hbl->is_destination_charges_paid, // 16
                    $status, // 17
                    $referencesString ? "SHIP NO. $referencesString" : null, // 18
                    ($hbl->branch['currency_symbol'] ?? '') . ' ' . ($hbl['grand_total'] ?? ''), // 19
                ];
            } catch (\Exception $e) {
                Log::error('Error processing HBL package: ' . $e->getMessage());
                continue;
            }
        }

        return $data;
    }

    private function getWarehouseCode($hbl): ?string
    {
        if (!$hbl) return null;

        if ($hbl->warehouse_id ?? null) {
            return match ($hbl->warehouse_id) {
                GetBranchByName::run(WarehouseType::COLOMBO->value)['id'] => 'CMB',
                GetBranchByName::run(WarehouseType::NINTAVUR->value)['id'] => 'NTR',
                default => null,
            };
        } elseif ($hbl->warehouse ?? null) {
            return match (ucwords($hbl->warehouse)) {
                WarehouseType::COLOMBO->value => 'CMB',
                WarehouseType::NINTAVUR->value => 'NTR',
                default => null,
            };
        }

        return null;
    }
}
