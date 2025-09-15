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
        $rows[] = ['UNIVERSAL FREIGHT SERVICES, DOHA, QATAR', '', '', '', '', '', '', '', '', '', '', ''];

        // Loading tally sheet title
        $rows[] = ['LOADING TALLY SHEET', '', '', '', '', '', '', '', '', '', '', ''];

        // Container info row
        $rows[] = [
            'CONTR NO :',
            $this->container->container_number ?? '',
            '',
            'DATE LOADED : '.Carbon::parse($this->container->loading_started_at ?? now())->format('d.m.Y'),
            '',
            '',
            '',
            '',
            '',
            'SHIPMENT NO:'.($this->container->reference ?? ''),
            '',
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
            'DESTINATION',
            'REMARKS',
        ];

        // Data rows
        $serialNumber = 28; // Starting from 28 as shown in the image
        $totalCBM = 0;
        $totalTOT = 0;

        foreach ($data as $item) {
            $packageTypes = is_array($item[4]) ? array_filter($item[4]) : [$item[4]];

            // Split package types into separate columns (up to 4 columns as shown in image)
            $packageCol1 = isset($packageTypes[0]) ? $packageTypes[0] : '';
            $packageCol2 = isset($packageTypes[1]) ? $packageTypes[1] : '';
            $packageCol3 = isset($packageTypes[2]) ? $packageTypes[2] : '';
            $packageCol4 = isset($packageTypes[3]) ? $packageTypes[3] : '';

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
                '',
                $item[5], // Destination
                $item[6] ?? '', // Remarks
            ];
        }

        // Add grand total row
        $rows[] = ['', '', 'GRAND TOTAL', number_format($totalCBM, 3), $totalTOT, '', '', '', '', '', '', ''];

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
            $data[] = [
                $hbl->hbl_number,
                $hbl->hbl_name,
                $hblPackages->sum('volume'),
                count($hblPackages),
                $hblPackages->pluck('package_type')->values()->all(),
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
                $sheet->mergeCells('A1:L1');

                // Merge loading tally sheet title cell
                $sheet->mergeCells('A2:L2');

                // Merge container info cells
                $sheet->mergeCells('A3:B3'); // CONTR NO
                $sheet->mergeCells('D3:I3'); // DATE LOADED
                $sheet->mergeCells('J3:L3'); // SHIPMENT NO

                // Merge TYPE OF PACKAGE columns in header row
                $sheet->mergeCells('F4:J4'); // TYPE OF PACKAGE

                // Set borders for all cells
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A1:L'.$highestRow)->applyFromArray([
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
                $sheet->getColumnDimension('F')->setWidth(8);   // TYPE OF PACKAGE 1
                $sheet->getColumnDimension('G')->setWidth(8);   // TYPE OF PACKAGE 2
                $sheet->getColumnDimension('H')->setWidth(8);   // TYPE OF PACKAGE 3
                $sheet->getColumnDimension('I')->setWidth(8);   // TYPE OF PACKAGE 4
                $sheet->getColumnDimension('J')->setWidth(8);   // Empty column
                $sheet->getColumnDimension('K')->setWidth(12);  // DESTINATION
                $sheet->getColumnDimension('L')->setWidth(15);  // REMARKS

                // Apply center alignment to data rows
                $sheet->getStyle('A5:L'.$highestRow)->applyFromArray([
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
                $sheet->getStyle('A'.$grandTotalRow.':L'.$grandTotalRow)->applyFromArray([
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
                $sheet->getStyle('C'.$grandTotalRow.':E'.$grandTotalRow)->applyFromArray([
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
