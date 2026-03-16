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

class OverLandReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    WithCustomStartCell,
    WithEvents
{
    protected $filters;
    protected $dateRange;
    protected $agentName;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;

        $dateFrom = !empty($filters['date_from']) ? date('d/m/Y', strtotime($filters['date_from'])) : '';
        $dateTo = !empty($filters['date_to']) ? date('d/m/Y', strtotime($filters['date_to'])) : '';

        if ($dateFrom && $dateTo) {
            $this->dateRange = "FROM {$dateFrom} TO {$dateTo}";
        } else {
            $this->dateRange = "FROM 01/01/2026 TO " . date('d/m/Y');
        }

        // Get agent name if filtered
        if (!empty($filters['branch_id'])) {
            $branch = DB::table('branches')->where('id', $filters['branch_id'])->first();
            $this->agentName = $branch ? $branch->name : 'ALL AGENTS';
        } else {
            $this->agentName = 'ALL AGENTS';
        }
    }

    public function query()
    {
        $query = DB::table('unloading_issues')
            ->join('hbl_packages', 'unloading_issues.hbl_package_id', '=', 'hbl_packages.id')
            ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
            ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
            ->leftJoin('duplicate_container_hbl_package as dchp', 'hbl_packages.id', '=', 'dchp.hbl_package_id')
            ->leftJoin('containers', 'dchp.container_id', '=', 'containers.id')
            ->select([
                'hbl.hbl_number',
                'consignees.name as consignee_name',
                'hbl.consignee_address as address',
                DB::raw('SUM(hbl_packages.quantity) as tot_pkg'),
                DB::raw('GROUP_CONCAT(DISTINCT hbl_packages.package_type) as typ_pkg'),
                DB::raw('MAX(containers.estimated_time_of_arrival) as arrival_date'),
                DB::raw('MAX(containers.unloading_ended_at) as destuff_date'),
                DB::raw('COUNT(unloading_issues.id) as over_qty')
            ])
            ->whereNull('unloading_issues.deleted_at')
            ->whereNull('hbl_packages.deleted_at')
            ->whereNull('hbl.deleted_at')
            ->where('unloading_issues.type', 'Overland')
            ->groupBy([
                'hbl.id',
                'hbl.hbl_number',
                'consignees.name',
                'hbl.consignee_address'
            ]);

        // Apply date range filters
        if (!empty($this->filters['date_from'])) {
            $query->where(function($q) {
                $q->whereDate('containers.unloading_ended_at', '>=', $this->filters['date_from'])
                  ->orWhere(function($subQ) {
                      $subQ->whereNull('containers.unloading_ended_at')
                           ->whereDate('unloading_issues.created_at', '>=', $this->filters['date_from']);
                  });
            });
        }

        if (!empty($this->filters['date_to'])) {
            $query->where(function($q) {
                $q->whereDate('containers.unloading_ended_at', '<=', $this->filters['date_to'])
                  ->orWhere(function($subQ) {
                      $subQ->whereNull('containers.unloading_ended_at')
                           ->whereDate('unloading_issues.created_at', '<=', $this->filters['date_to']);
                  });
            });
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('hbl.hbl_number', 'like', "%{$search}%")
                    ->orWhere('consignees.name', 'like', "%{$search}%");
            });
        }

        // Apply branch filter
        if (!empty($this->filters['branch_id'])) {
            $query->where('hbl.branch_id', $this->filters['branch_id']);
        }

        return $query->orderBy(DB::raw('COALESCE(MAX(containers.unloading_ended_at), MAX(unloading_issues.created_at))'), 'desc');
    }

    public function map($row): array
    {
        return [
            $row->hbl_number,
            $row->consignee_name,
            $row->address,
            $row->tot_pkg ?: 0,
            $row->typ_pkg ?: '',
            $row->arrival_date ? date('d/m/Y', strtotime($row->arrival_date)) : '',
            $row->destuff_date ? date('d/m/Y', strtotime($row->destuff_date)) : '',
            $row->over_qty ?: 0,
        ];
    }

    public function headings(): array
    {
        $printedDateTime = date('d/m/Y H:i:s');

        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['OVER LAND REPORT ' . $this->dateRange],
            ['AGENT NAME : ' . $this->agentName],
            [$printedDateTime],
            [],
            [
                'HBL No',
                'Consignee Name',
                'Address',
                'Tot PKG',
                'Typ PKG',
                'Arrival Date',
                'Destuff Date',
                'Over Qty',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            4 => [
                'font' => ['bold' => false, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            6 => [
                'font' => ['bold' => true, 'size' => 9],
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
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Over Land Report';
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

                // Set page setup for A4 size landscape
                $sheet->getPageSetup()
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                // Merge title cells
                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');
                $sheet->mergeCells('A3:H3');
                $sheet->mergeCells('A4:H4');

                // Remove borders from header rows (1-5)
                for ($row = 1; $row <= 5; $row++) {
                    for ($col = 'A'; $col <= 'H'; $col++) {
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
                $sheet->getColumnDimension('A')->setWidth(12); // HBL No
                $sheet->getColumnDimension('B')->setWidth(25); // Consignee Name
                $sheet->getColumnDimension('C')->setWidth(30); // Address
                $sheet->getColumnDimension('D')->setWidth(8);  // Tot PKG
                $sheet->getColumnDimension('E')->setWidth(12); // Typ PKG
                $sheet->getColumnDimension('F')->setWidth(12); // Arrival Date
                $sheet->getColumnDimension('G')->setWidth(12); // Destuff Date
                $sheet->getColumnDimension('H')->setWidth(8);  // Over Qty

                // Set header row height
                $sheet->getRowDimension(6)->setRowHeight(30);

                // Get the last row with data
                $lastRow = $sheet->getHighestRow();

                if ($lastRow > 6) {
                    // Apply borders to data rows
                    $sheet->getStyle('A6:H' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'font' => ['size' => 8],
                    ]);

                    // Center align numeric columns
                    $sheet->getStyle('D7:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('H7:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Center align dates
                    $sheet->getStyle('F7:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
