<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BondStorageRecordsExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    WithCustomStartCell,
    WithEvents
{
    protected $filters;
    protected $containerInfo;
    protected $agentName;
    protected $rowCount = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;

        // Get container and agent info for header
        $this->getContainerInfo();
    }

    protected function getContainerInfo()
    {
        $query = DB::table('hbl_packages')
            ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
            ->join('branches', 'hbl.branch_id', '=', 'branches.id')
            ->leftJoin('container_hbl_package', 'hbl_packages.id', '=', 'container_hbl_package.hbl_package_id')
            ->leftJoin('containers', 'container_hbl_package.container_id', '=', 'containers.id')
            ->leftJoin('detain_records', function($join) {
                $join->on('hbl_packages.id', '=', 'detain_records.rtfable_id')
                     ->where('detain_records.rtfable_type', '=', 'App\\Models\\HBLPackage');
            })
            ->whereNotNull('hbl_packages.bond_storage_number')
            ->whereNull('hbl.deleted_at')
            ->whereNull('hbl_packages.deleted_at');

        // Apply filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('hbl_packages.created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('hbl_packages.created_at', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['agent_id'])) {
            $query->where('hbl.branch_id', $this->filters['agent_id']);
        }

        $info = $query->select([
            'containers.reference',
            'containers.vessel_name',
            'containers.unloading_ended_at',
            'branches.name as agent_name',
            'hbl.reference as hbl_reference'
        ])->first();

        $this->containerInfo = $info;
        $this->agentName = $info->agent_name ?? 'ALL AGENTS';
    }

    public function map($row): array
    {
        return [
            $row->hbl_number,
            $row->consignee_name,
            $row->pkgs,
            $row->bs_no,
            $row->remarks,
        ];
    }

    public function headings(): array
    {
        $referenceNo = $this->containerInfo->reference ?? '';
        $vesselName = $this->containerInfo->vessel_name ?? '';
        $containerNo = '';
        $destuffingDate = $this->containerInfo->unloading_ended_at
            ? date('d/m/Y', strtotime($this->containerInfo->unloading_ended_at))
            : '';
        $hblReference = $this->containerInfo->hbl_reference ?? '';

        return [
            [date('d/m/Y'), date('H:i:s')],
            [],
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Bond Storage Records'],
            [],
            ['REFERENCE NO', $referenceNo, '', 'DESTUFFING DAT', $destuffingDate],
            ['VESSEL NAME', $vesselName, '', 'B.B. NO'],
            ['CONTAINER NO', $containerNo, '', 'T.T. NO'],
            ['AGENT NAME', $this->agentName, '', 'E. NO'],
            ['OBL / NO', $hblReference, '', '',],
            [],
            [
                'HBL No',
                'Name of Consignee',
                'PKgs',
                'B / S No',
                'Remarks',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            3 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            4 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            6 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            7 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            8 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            9 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            10 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            12 => [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Bond Storage Records';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Set page setup for A4 size portrait
                $sheet->getPageSetup()
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);

                // Merge title cells
                $sheet->mergeCells('A3:E3');
                $sheet->mergeCells('A4:E4');

                // Remove borders from header rows (1-11)
                for ($row = 1; $row <= 11; $row++) {
                    for ($col = 'A'; $col <= 'E'; $col++) {
                        $sheet->getStyle($col . $row)->applyFromArray([
                            'borders' => [
                                'top' => ['borderStyle' => Border::BORDER_NONE],
                                'bottom' => ['borderStyle' => Border::BORDER_NONE],
                                'left' => ['borderStyle' => Border::BORDER_NONE],
                                'right' => ['borderStyle' => Border::BORDER_NONE],
                            ],
                        ]);
                    }
                }

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(15); // HBL No
                $sheet->getColumnDimension('B')->setWidth(35); // Name of Consignee
                $sheet->getColumnDimension('C')->setWidth(8);  // PKgs
                $sheet->getColumnDimension('D')->setWidth(15); // B / S No
                $sheet->getColumnDimension('E')->setWidth(25); // Remarks

                // Get the last row with data
                $lastRow = $sheet->getHighestRow();

                if ($lastRow > 12) {
                    // Apply borders to data rows (starting from row 12)
                    $sheet->getStyle('A12:E' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'font' => ['size' => 9],
                    ]);

                    // Center align package count
                    $sheet->getStyle('C13:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Add total row
                    $totalPackages = $this->query()->sum('hbl_packages.quantity');
                    $totalConsignees = $this->query()->distinct('hbl.consignee_id')->count('hbl.consignee_id');

                    $sheet->setCellValue('A' . ($lastRow + 1), 'No of Packages');
                    $sheet->setCellValue('C' . ($lastRow + 1), $totalPackages);

                    $sheet->setCellValue('A' . ($lastRow + 3), 'Total No. of Packages');
                    $sheet->setCellValue('C' . ($lastRow + 3), ': ' . $totalPackages);

                    $sheet->setCellValue('A' . ($lastRow + 4), 'Total No. of Consignees');
                    $sheet->setCellValue('C' . ($lastRow + 4), ': ' . $totalConsignees);

                    // Style total rows
                    $sheet->getStyle('A' . ($lastRow + 1) . ':E' . ($lastRow + 1))->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'font' => ['bold' => true],
                    ]);
                }
            },
        ];
    }

    public function query()
    {
        $query = DB::table('hbl_packages')
            ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
            ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
            ->join('branches', 'hbl.branch_id', '=', 'branches.id')
            ->leftJoin('container_hbl_package', 'hbl_packages.id', '=', 'container_hbl_package.hbl_package_id')
            ->leftJoin('containers', 'container_hbl_package.container_id', '=', 'containers.id')
            ->leftJoin('detain_records', function($join) {
                $join->on('hbl_packages.id', '=', 'detain_records.rtfable_id')
                     ->where('detain_records.rtfable_type', '=', 'App\\Models\\HBLPackage');
            })
            ->select([
                'hbl.hbl_number',
                'consignees.name as consignee_name',
                'hbl_packages.quantity as pkgs',
                'hbl_packages.bond_storage_number as bs_no',
                'detain_records.note as remarks'
            ])
            ->whereNotNull('hbl_packages.bond_storage_number')
            ->whereNull('hbl.deleted_at')
            ->whereNull('hbl_packages.deleted_at');

        // Apply filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('hbl_packages.created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('hbl_packages.created_at', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['agent_id'])) {
            $query->where('hbl.branch_id', $this->filters['agent_id']);
        }
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('hbl.hbl_number', 'like', "%{$search}%")
                    ->orWhere('hbl_packages.bond_storage_number', 'like', "%{$search}%")
                    ->orWhere('consignees.name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('hbl_packages.created_at', 'desc');
    }
}
