<?php

namespace App\Exports;

use App\Actions\Branch\GetBranchByName;
use App\Actions\MHBL\GetMHBLById;
use App\Actions\Setting\GetSettings;
use App\Actions\User\GetUserCurrentBranch;
use App\Enum\WarehouseType;
use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LoadedContainerManifestExcelExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithCustomStartCell, WithEvents
{
    private Container $container;
    private $settings;
    private $branch;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->settings = GetSettings::run();
        $this->branch = GetUserCurrentBranch::run();
    }

    public function collection()
    {
        $data = $this->prepareData();
        $collection = collect();
        
        $serialNumber = 1;
        foreach ($data as $row) {
            $packages = $row[9]; // packages collection
            
            if ($packages && $packages->count() > 0) {
                // Group packages by type for better display
                $packageTypes = [];
                $totalCbm = 0;
                $totalQuantity = 0;
                
                foreach ($packages as $package) {
                    $type = $package->package_type ?? 'N/A';
                    if (!isset($packageTypes[$type])) {
                        $packageTypes[$type] = 0;
                    }
                    $packageTypes[$type] += $package->quantity ?? 0;
                    $totalCbm += $package->cbm ?? 0;
                    $totalQuantity += $package->quantity ?? 0;
                }
                
                // Create package type string
                $packageTypeString = '';
                foreach ($packageTypes as $type => $qty) {
                    $packageTypeString .= $qty . ' ' . $type . ' ';
                }
                
                $collection->push([
                    $serialNumber++,
                    $row[0], // HBL Number
                    $row[1], // Customer Name
                    number_format($totalCbm, 3), // CBM
                    $totalQuantity, // Total packages
                    trim($packageTypeString), // Package types
                    '', // Empty column for spacing
                    '', // Empty column for spacing
                    '', // Empty column for spacing
                    '', // Empty column for spacing
                    '', // Empty column for spacing
                    '', // Empty column for spacing
                    '', // Empty column for spacing
                    $row[13] ?? '', // Destination/Warehouse
                    $row[18] ?? '', // Remarks
                ]);
            } else {
                $collection->push([
                    $serialNumber++,
                    $row[0], // HBL Number
                    $row[1], // Customer Name
                    '0.000', // CBM
                    '0', // Total packages
                    '', // Package types
                    '', // Empty column for spacing
                    '', // Empty column for spacing
                    '', // Empty column for spacing
                    '', // Empty column for spacing
                    '', // Empty column for spacing
                    '', // Empty column for spacing
                    '', // Empty column for spacing
                    $row[13] ?? '', // Destination/Warehouse
                    $row[18] ?? '', // Remarks
                ]);
            }
        }
        
        return $collection;
    }

    public function headings(): array
    {
        return [
            'SN',
            'HBL',
            'NAME OF CUSTOMER',
            'CBM',
            'TOT',
            'TYPE OF PACKAGE',
            '', // Empty columns for package type breakdown
            '',
            '',
            '',
            '',
            '',
            '',
            'DESTINATION',
            'REMARKS'
        ];
    }

    public function startCell(): string
    {
        return 'A4'; // Start data from row 4 to leave space for header
    }

    public function title(): string
    {
        return 'Loading Tally Sheet';
    }

    public function styles(Worksheet $sheet)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(5);  // SN
        $sheet->getColumnDimension('B')->setWidth(12); // HBL
        $sheet->getColumnDimension('C')->setWidth(25); // NAME OF CUSTOMER
        $sheet->getColumnDimension('D')->setWidth(8);  // CBM
        $sheet->getColumnDimension('E')->setWidth(6);  // TOT
        $sheet->getColumnDimension('F')->setWidth(15); // TYPE OF PACKAGE
        $sheet->getColumnDimension('G')->setWidth(8);
        $sheet->getColumnDimension('H')->setWidth(8);
        $sheet->getColumnDimension('I')->setWidth(8);
        $sheet->getColumnDimension('J')->setWidth(8);
        $sheet->getColumnDimension('K')->setWidth(8);
        $sheet->getColumnDimension('L')->setWidth(8);
        $sheet->getColumnDimension('M')->setWidth(8);
        $sheet->getColumnDimension('N')->setWidth(15); // DESTINATION
        $sheet->getColumnDimension('O')->setWidth(20); // REMARKS

        return [
            // Header row styling
            4 => [
                'font' => [
                    'bold' => true,
                    'size' => 10,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add company header
                $sheet->setCellValue('A1', strtoupper($this->settings->company_name ?? 'UNIVERSAL FREIGHT SERVICES, DOHA, QATAR'));
                $sheet->mergeCells('A1:O1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Add title
                $sheet->setCellValue('A2', 'LOADING TALLY SHEET');
                $sheet->mergeCells('A2:O2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Add container info
                $containerInfo = sprintf(
                    'CONTR NO: %s     DATE LOADED: %s     SHIPMENT NO: %s',
                    $this->container->container_number ?? 'N/A',
                    $this->container->created_at ? $this->container->created_at->format('d.m.Y') : date('d.m.Y'),
                    $this->container->reference ?? 'N/A'
                );
                $sheet->setCellValue('A3', $containerInfo);
                $sheet->mergeCells('A3:O3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'size' => 10,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Get the last row with data
                $lastRow = $sheet->getHighestRow();
                
                // Apply borders to all data cells
                $dataRange = 'A4:O' . $lastRow;
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Center align specific columns
                $sheet->getStyle('A4:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // SN
                $sheet->getStyle('D4:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // CBM
                $sheet->getStyle('E4:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // TOT
                $sheet->getStyle('N4:N' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // DESTINATION

                // Set row height for better appearance
                for ($i = 4; $i <= $lastRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(20);
                }
            },
        ];
    }

    private function prepareData(): array
    {
        $data = [];
        $loadedMHBLPackages = [];
        $loadedHBLPackages = [];

        // Get the currently loaded HBL package IDs
        $currentlyLoadedPackageIds = $this->container->hbl_packages->pluck('id')->toArray();

        // Group packages by HBL, but only include packages that are still loaded in the container
        foreach ($this->container->duplicate_hbl_packages->groupBy('hbl_id') as $hblId => $packages) {
            // Filter packages to only include those that are still in the container's hbl_packages
            $stillLoadedPackages = $packages->filter(function ($package) use ($currentlyLoadedPackageIds) {
                return in_array($package->id, $currentlyLoadedPackageIds);
            });

            // Skip this HBL if all its packages have been removed from the container
            if ($stillLoadedPackages->isEmpty()) {
                continue;
            }

            $hbl = HBL::withoutGlobalScope(BranchScope::class)->with('mhbl')->find($hblId);
            if ($hbl->mhbl) {
                $loadedMHBLPackages[$hbl->mhbl->id][] = $stillLoadedPackages;
            } else {
                $loadedHBLPackages[$hblId] = [
                    'hbl' => $hbl,
                    'packages' => $stillLoadedPackages,
                ];
            }
        }

        //  MHBL packages
        foreach ($loadedMHBLPackages as $mhblId => $mhblPackage) {
            $mhbl = GetMHBLById::run($mhblId);
            $hblPackages = [];
            if (! empty($mhbl->hbls)) {
                foreach ($mhbl->hbls as $mhblHBL) {
                    foreach ($mhblHBL->packages as $hblPackage) {
                        $hblPackages[] = $hblPackage;
                    }
                }
            }

            $warehouse = null;

            if ($mhbl->hbls[0]->warehouse_id) {
                $warehouse = match ($mhbl->hbls[0]->warehouse_id) {
                    GetBranchByName::run(WarehouseType::COLOMBO->value)['id'] => 'CMB',
                    GetBranchByName::run(WarehouseType::NINTAVUR->value)['id'] => 'NTR',
                    default => null,
                };
            } elseif ($mhbl->hbls[0]->warehouse) {
                $warehouse = match (ucwords($mhbl->hbls[0]->warehouse)) {
                    WarehouseType::COLOMBO->value => 'CMB',
                    WarehouseType::NINTAVUR->value => 'NTR',
                    default => null,
                };
            }

            $data[] = [
                $mhbl->hbl_number ?: $mhbl->reference,
                $mhbl->shipper->name ?? '',
                $mhbl->shipper->address ?? '',
                $mhbl->shipper->pp_or_nic_no ?? '',
                $mhbl->shipper->mobile_number ?? '',
                $mhbl->consignee->name ?? '',
                $mhbl->consignee->address ?? '',
                $mhbl->consignee->pp_or_nic_no ?? '',
                $mhbl->consignee->mobile_number ?? '',
                collect($hblPackages ?? []),
                $mhbl->hbls[0]->paid_amount > 0 ? 'PAID' : 'UNPAID',
                'Gift',
                '',
                $warehouse,
                '',
                1,
                0,
                null,
                null,
                null,
            ];
        }

        // HBL packages
        foreach ($loadedHBLPackages as $hblData) {
            $hbl = $hblData['hbl'];

            $warehouse = null;

            if ($hbl->warehouse_id) {
                $warehouse = match ($hbl->warehouse_id) {
                    GetBranchByName::run(WarehouseType::COLOMBO->value)['id'] => 'CMB',
                    GetBranchByName::run(WarehouseType::NINTAVUR->value)['id'] => 'NTR',
                    default => null,
                };
            } elseif ($hbl->warehouse) {
                $warehouse = match (ucwords($hbl->warehouse)) {
                    WarehouseType::COLOMBO->value => 'CMB',
                    WarehouseType::NINTAVUR->value => 'NTR',
                    default => null,
                };
            }

            $isHBLFullLoad = $hbl->packages->every(fn ($package) => $package->duplicate_containers->isNotEmpty());
            $hblLoadedContainers = $hbl->packages
                ->load('duplicate_containers')
                ->pluck('duplicate_containers')
                ->flatten()
                ->unique('id')
                ->sortByDesc('created_at');
            $hblLoadedLatestContainer = $hbl->packages
                ->load('duplicate_containers')
                ->pluck('duplicate_containers')
                ->flatten()
                ->unique('id')
                ->sortByDesc('created_at')
                ->first();
            if ($isHBLFullLoad && count($hblLoadedContainers) === 1) {
                $status = '';
            } elseif (count($hblLoadedContainers) > 1 && $hblLoadedLatestContainer['id'] === $this->container['id']) {
                $status = 'BALANCE';
            } else {
                $status = 'SHORT LOADED';
            }
            $loadedContainerReferences = $hbl->packages->load('duplicate_containers')
                ->pluck('duplicate_containers')
                ->flatten()
                ->pluck('reference')
                ->unique();
            $filteredReferences = $loadedContainerReferences->reject(function ($ref) {
                return $ref == $this->container['reference'];
            });
            $referencesString = $filteredReferences->implode(',');

            $data[] = [
                $hbl->hbl_number ?: $hbl->reference,
                $hbl->hbl_name,
                $hbl->address,
                $hbl->nic,
                $hbl->contact_number,
                $hbl->consignee_name,
                $hbl->consignee_address,
                $hbl->consignee_nic,
                $hbl->consignee_contact.($hbl->consignee_additional_mobile_number ? '/'.$hbl->consignee_additional_mobile_number : ''),
                $loadedHBLPackages[$hbl->id]['packages'],
                $hbl->paid_amount > 0 ? 'PAID' : 'UNPAID',
                $hbl->hbl_type,
                $hbl->other_charge,
                $warehouse,
                $hbl->iq_number,
                $hbl->is_departure_charges_paid,
                $hbl->is_destination_charges_paid,
                $status,
                $referencesString ? "SHIP NO. $referencesString" : null,
                $hbl->branch['currency_symbol'].' '.$hbl['grand_total'],
            ];
        }

        return $data;
    }
}