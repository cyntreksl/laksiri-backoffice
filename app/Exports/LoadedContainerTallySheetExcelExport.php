<?php

namespace App\Exports;

use App\Actions\HBL\GetHBLByHBLNumber;
use App\Models\Container;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoadedContainerTallySheetExcelExport implements FromArray, ShouldAutoSize, WithEvents, WithStyles
{
    use Exportable;

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function array(): array
    {
        $data = $this->prepareData();
        $rows = [];

        // Company header row
        $rows[] = ['UNIVERSAL FREIGHT SERVICES, DOHA, QATAR', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];

        // Loading tally sheet title
        $rows[] = ['LOADING TALLY SHEET', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];

        // Container info row
        $rows[] = [
            'CONTR NO : '.($this->container->container_number ?? ''),
            '',
            '',
            'DATE LOADED : '.Carbon::parse($this->container->loading_started_at ?? now())->format('d.m.Y'),
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'SHIPMENT NO:'.($this->container->reference ?? ''),
            '',
        ];

        // Column headers
        $rows[] = [
            'SN',
            'HBL',
            'NAME OF CUSTOMER',
            'CBM',
            'TOT',
            'TYPE OF PACKAGE',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'DESTINATION',
            'REMARKS',
        ];

        // Data rows
        $serialNumber = 1; // Starting from 1 to match PDF format
        $totalCBM = 0;
        $totalTOT = 0;

        foreach ($data as $item) {
            $packageTypes = is_array($item[4]) ? array_filter($item[4]) : [$item[4]];

            // Split package types into separate columns (up to 9 columns to match PDF format)
            $packageCol1 = isset($packageTypes[0]) ? $packageTypes[0] : '';
            $packageCol2 = isset($packageTypes[1]) ? $packageTypes[1] : '';
            $packageCol3 = isset($packageTypes[2]) ? $packageTypes[2] : '';
            $packageCol4 = isset($packageTypes[3]) ? $packageTypes[3] : '';
            $packageCol5 = isset($packageTypes[4]) ? $packageTypes[4] : '';
            $packageCol6 = isset($packageTypes[5]) ? $packageTypes[5] : '';
            $packageCol7 = isset($packageTypes[6]) ? $packageTypes[6] : '';
            $packageCol8 = isset($packageTypes[7]) ? $packageTypes[7] : '';
            $packageCol9 = isset($packageTypes[8]) ? $packageTypes[8] : '';

            $cbm = number_format($item[2], 3);
            $tot = $item[3];

            $totalCBM += $item[2];
            $totalTOT += $tot;

            $rows[] = [
                $serialNumber++,
                $item[0], // HBL
                strtoupper($item[1]), // Customer Name
                $cbm, // CBM with 3 decimal places
                $tot, // TOT
                $packageCol1,
                $packageCol2,
                $packageCol3,
                $packageCol4,
                $packageCol5,
                $packageCol6,
                $packageCol7,
                $packageCol8,
                $packageCol9,
                $item[5], // Destination
                $item[6] ?? '', // Remarks
            ];
        }

        // Add grand total row
        $rows[] = ['', '', 'GRAND TOTAL', number_format($totalCBM, 3), $totalTOT, '', '', '', '', '', '', '', '', '', '', ''];

        return $rows;
    }

    private function prepareData(): array
    {
        $data = [];

        // Get the currently loaded HBL package IDs
        $currentlyLoadedPackageIds = $this->container->hbl_packages->pluck('id')->toArray();

        // Filter duplicate_hbl_packages to only include those that are still in the container's hbl_packages
        $filteredPackages = $this->container->load('duplicate_hbl_packages.hbl.mhbl')->duplicate_hbl_packages->filter(function ($package) use ($currentlyLoadedPackageIds) {
            return in_array($package->id, $currentlyLoadedPackageIds);
        });

        $groupedPackages = $filteredPackages->groupBy(function ($package) {
            return $package->hbl->hbl_number;
        });

        foreach ($groupedPackages as $hblNumber => $hblPackages) {
            $hbl = GetHBLByHBLNumber::run($hblNumber);
            
            // Get all package types and ensure we have all 9 slots for consistency with PDF
            $packageTypes = $hblPackages->pluck('package_type')->filter()->values()->all();
            
            // Pad array to 9 elements to match PDF template (which uses indices 0-8)
            $packageTypes = array_pad($packageTypes, 9, '');
            
            $data[] = [
                $hbl->hbl_number,
                $hbl->hbl_name,
                $hblPackages->sum('volume'),
                count($hblPackages),
                $packageTypes, // Now contains up to 9 package types
                $hbl->warehouse === 'COLOMBO' ? 'CMB' : 'NTR',
                '',
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Company header row styling
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']],
            ],
            // Loading tally sheet title row styling
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E8E8E8']],
            ],
            // Info row styling
            3 => [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']],
            ],
            // Column headers styling
            4 => [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E8E8E8']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge company header cell
                $sheet->mergeCells('A1:P1');

                // Merge loading tally sheet title cell
                $sheet->mergeCells('A2:P2');

                // Merge container info cells
                $sheet->mergeCells('A3:C3'); // CONTR NO
                $sheet->mergeCells('D3:M3'); // DATE LOADED
                $sheet->mergeCells('N3:P3'); // SHIPMENT NO

                // Merge TYPE OF PACKAGE columns in header row
                $sheet->mergeCells('F4:N4'); // TYPE OF PACKAGE

                // Set borders for all cells
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A1:P'.$highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(25);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(20);

                // Set column widths to match the image layout
                $sheet->getColumnDimension('A')->setWidth(6);   // SN
                $sheet->getColumnDimension('B')->setWidth(10);  // HBL
                $sheet->getColumnDimension('C')->setWidth(20);  // NAME OF CUSTOMER
                $sheet->getColumnDimension('D')->setWidth(8);   // CBM
                $sheet->getColumnDimension('E')->setWidth(6);   // TOT
                $sheet->getColumnDimension('F')->setWidth(6);   // TYPE OF PACKAGE 1
                $sheet->getColumnDimension('G')->setWidth(6);   // TYPE OF PACKAGE 2
                $sheet->getColumnDimension('H')->setWidth(6);   // TYPE OF PACKAGE 3
                $sheet->getColumnDimension('I')->setWidth(6);   // TYPE OF PACKAGE 4
                $sheet->getColumnDimension('J')->setWidth(6);   // TYPE OF PACKAGE 5
                $sheet->getColumnDimension('K')->setWidth(6);   // TYPE OF PACKAGE 6
                $sheet->getColumnDimension('L')->setWidth(6);   // TYPE OF PACKAGE 7
                $sheet->getColumnDimension('M')->setWidth(6);   // TYPE OF PACKAGE 8
                $sheet->getColumnDimension('N')->setWidth(6);   // TYPE OF PACKAGE 9
                $sheet->getColumnDimension('O')->setWidth(12);  // DESTINATION
                $sheet->getColumnDimension('P')->setWidth(15);  // REMARKS

                // Apply center alignment to data rows
                $sheet->getStyle('A5:P'.$highestRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Left align customer names (column C)
                $sheet->getStyle('C5:C'.$highestRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Apply special styling for grand total row
                $grandTotalRow = $highestRow;

                // Remove all borders from grand total row first
                $sheet->getStyle('A'.$grandTotalRow.':P'.$grandTotalRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Apply bottom border only to specific cells (C, D, E for GRAND TOTAL, CBM, TOT)
                $sheet->getStyle('D'.$grandTotalRow.':E'.$grandTotalRow)->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
