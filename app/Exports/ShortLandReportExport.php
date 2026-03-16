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

class ShortLandReportExport implements
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
    protected $rowCount = 0;

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
        if (!empty($filters['agent_id'])) {
            $agent = DB::table('branches')->where('id', $filters['agent_id'])->first();
            $this->agentName = $agent ? $agent->name : 'ALL AGENTS';
        } else {
            $this->agentName = 'ALL AGENTS';
        }
    }

    public function query()
    {
        $query = DB::table('hbl')
            ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
            ->join('branches', 'hbl.branch_id', '=', 'branches.id')
            ->leftJoin('hbl_packages', 'hbl.id', '=', 'hbl_packages.hbl_id')
            ->leftJoin('container_hbl_package', 'hbl_packages.id', '=', 'container_hbl_package.hbl_package_id')
            ->leftJoin('containers', 'container_hbl_package.container_id', '=', 'containers.id')
            ->select([
                'hbl.hbl_number',
                'hbl.reference',
                'consignees.name as consignee_name',
                'hbl.consignee_address as address',
                'branches.name as agent_name',
                DB::raw('MAX(containers.estimated_time_of_arrival) as arrival_date'),
                DB::raw('MAX(containers.unloading_ended_at) as destuff_date'),
                DB::raw('COUNT(hbl_packages.id) as main_qty'),
                DB::raw('GROUP_CONCAT(DISTINCT hbl_packages.package_type) as typ_pkg'),
                DB::raw('SUM(CASE WHEN hbl_packages.is_shortland = 1 THEN 1 ELSE 0 END) as short_qty'),
                DB::raw('SUM(CASE WHEN hbl_packages.is_shortland = 0 OR hbl_packages.is_shortland IS NULL THEN 1 ELSE 0 END) as reci_qty')
            ])
            ->whereNull('hbl.deleted_at')
            ->whereNull('hbl_packages.deleted_at')
            ->where('hbl.is_shortland', true)
            ->groupBy([
                'hbl.id',
                'hbl.hbl_number',
                'hbl.reference',
                'consignees.name',
                'hbl.consignee_address',
                'branches.name'
            ]);

        // Apply date range filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('hbl.created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('hbl.created_at', '<=', $this->filters['date_to']);
        }

        // Apply agent filter
        if (!empty($this->filters['agent_id'])) {
            $query->where('hbl.branch_id', $this->filters['agent_id']);
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('hbl.hbl_number', 'like', "%{$search}%")
                    ->orWhere('hbl.reference', 'like', "%{$search}%")
                    ->orWhere('consignees.name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('hbl.created_at', 'desc');
    }

    public function map($row): array
    {
        return [
            $row->hbl_number,
            $row->reference,
            $row->consignee_name,
            $row->address,
            $row->main_qty,
            $row->typ_pkg,
            $row->arrival_date ? date('d/m/Y', strtotime($row->arrival_date)) : '',
            $row->destuff_date ? date('d/m/Y', strtotime($row->destuff_date)) : '',
            $row->short_qty,
            $row->reci_qty,
        ];
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['SHORT LAND ' . $this->dateRange],
            ['AGENT NAME : ' . $this->agentName, '', '', '', '', '', '', '', '', ''],
            [],
            [
                'HBL No.',
                'Ref. No.',
                'Consignee Name',
                'Address',
                'Mani. Qty',
                'Typ Pkg',
                'Arrival Date',
                'Destuf. Date',
                'Short Qty',
                'Reci. Qty',
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
            5 => [
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
        return 'Short Land Report';
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

                // Set header and footer for page numbers
                $sheet->getHeaderFooter()
                    ->setOddFooter('&RPAGE : &P');

                // Merge title cells
                $sheet->mergeCells('A1:J1');
                $sheet->mergeCells('A2:J2');

                // Remove borders from header rows (1-4)
                for ($row = 1; $row <= 4; $row++) {
                    for ($col = 'A'; $col <= 'J'; $col++) {
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
                $sheet->getColumnDimension('A')->setWidth(10); // HBL No.
                $sheet->getColumnDimension('B')->setWidth(10); // Ref. No.
                $sheet->getColumnDimension('C')->setWidth(20); // Consignee Name
                $sheet->getColumnDimension('D')->setWidth(20); // Address
                $sheet->getColumnDimension('E')->setWidth(7);  // Mani. Qty
                $sheet->getColumnDimension('F')->setWidth(12); // Typ Pkg
                $sheet->getColumnDimension('G')->setWidth(10); // Arrival Date
                $sheet->getColumnDimension('H')->setWidth(10); // Destuf. Date
                $sheet->getColumnDimension('I')->setWidth(7);  // Short Qty
                $sheet->getColumnDimension('J')->setWidth(7);  // Reci. Qty

                // Set header row height to accommodate text
                $sheet->getRowDimension(5)->setRowHeight(30);

                // Get the last row with data
                $lastRow = $sheet->getHighestRow();

                if ($lastRow > 5) {
                    // Apply borders to data rows (starting from row 5)
                    $sheet->getStyle('A5:J' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'font' => ['size' => 8],
                    ]);

                    // Center align numeric columns
                    $sheet->getStyle('E6:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('I6:J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Center align dates
                    $sheet->getStyle('G6:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
