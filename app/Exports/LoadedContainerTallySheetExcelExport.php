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

        // Header row - LOADING TALLY SHEET
        $rows[] = ['LOADING TALLY SHEET', '', '', '', '', '', '', ''];

        // Container info row
        $rows[] = [
            'CONTR NO: '.($this->container->container_number ?? ''),
            '',
            'DATE LOADED: '.Carbon::parse($this->container->loading_started_at ?? now())->format('Y-m-d'),
            '',
            '',
            '',
            'SHIPMENT NO: '.($this->container->reference ?? ''),
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
            'DESTINATION',
            'REMARKS',
        ];

        // Data rows
        $serialNumber = 1;
        foreach ($data as $item) {
            $packageTypes = is_array($item[4]) ? implode(', ', array_filter($item[4])) : '';

            $rows[] = [
                $serialNumber++,
                $item[0], // HBL
                strtoupper($item[1]), // Customer Name
                number_format($item[2], 3), // CBM with 3 decimal places
                $item[3], // TOT
                $packageTypes, // Package Types combined
                $item[5], // Destination
                $item[6] ?? '', // Remarks
            ];
        }

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
            // Header row styling
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E8E8E8']],
            ],
            // Info row styling
            2 => [
                'font' => ['bold' => true, 'size' => 9],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F5F5F5']],
            ],
            // Column headers styling
            3 => [
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

                // Merge header cell
                $sheet->mergeCells('A1:H1');

                // Merge container info cells
                $sheet->mergeCells('A2:B2'); // CONTR NO
                $sheet->mergeCells('C2:F2'); // DATE LOADED
                $sheet->mergeCells('G2:H2'); // SHIPMENT NO

                // Set borders for all cells
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A1:H'.$highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(6);   // SN
                $sheet->getColumnDimension('B')->setWidth(12);  // HBL
                $sheet->getColumnDimension('C')->setWidth(20);  // NAME OF CUSTOMER
                $sheet->getColumnDimension('D')->setWidth(8);   // CBM
                $sheet->getColumnDimension('E')->setWidth(6);   // TOT
                $sheet->getColumnDimension('F')->setWidth(15);  // TYPE OF PACKAGE
                $sheet->getColumnDimension('G')->setWidth(12);  // DESTINATION
                $sheet->getColumnDimension('H')->setWidth(12);  // REMARKS

                // Apply center alignment to data rows
                $sheet->getStyle('A4:H'.$highestRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Left align customer names (column C)
                $sheet->getStyle('C4:C'.$highestRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Apply word wrap for TYPE OF PACKAGE column
                $sheet->getStyle('F4:F'.$highestRow)->applyFromArray([
                    'alignment' => [
                        'wrapText' => true,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}
