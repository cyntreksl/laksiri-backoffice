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

class AgeAnalysisConsigneeExport implements
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
    protected $agentFromName;
    protected $agentToName;
    protected $containerInfo;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;

        $dateFrom = !empty($filters['date_from']) ? date('d/m/Y', strtotime($filters['date_from'])) : '';
        $dateTo = !empty($filters['date_to']) ? date('d/m/Y', strtotime($filters['date_to'])) : '';

        if ($dateFrom && $dateTo) {
            $this->dateRange = "from {$dateFrom} To {$dateTo}";
        } else {
            $this->dateRange = "from 01/01/2026 To " . date('d/m/Y');
        }

        // Get agent from name
        if (!empty($filters['agent_from'])) {
            $branch = DB::table('branches')->where('id', $filters['agent_from'])->first();
            $this->agentFromName = $branch ? $branch->name : 'ALL';
        } else {
            $this->agentFromName = 'ALL';
        }

        // Get agent to name
        if (!empty($filters['agent_to'])) {
            $branch = DB::table('branches')->where('id', $filters['agent_to'])->first();
            $this->agentToName = $branch ? $branch->name : 'ALL';
        } else {
            $this->agentToName = 'ALL';
        }

        // Get container information
        if (!empty($filters['container_id'])) {
            $container = DB::table('containers')->where('id', $filters['container_id'])->first();
            $this->containerInfo = [
                'number' => $container->container_number ?? '',
                'reference' => $container->reference ?? '',
            ];
        } else {
            $this->containerInfo = null;
        }
    }

    public function query()
    {
        // Check if container filter is applied - use different query structure
        if (!empty($this->filters['container_id'])) {
            // When filtering by container, use INNER JOINs to get only HBLs linked to that container
            $query = DB::table('hbl')
                ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                ->join('branches as agent_from', 'hbl.branch_id', '=', 'agent_from.id')
                ->leftJoin('branches as agent_to', 'hbl.warehouse_id', '=', 'agent_to.id')
                ->join('hbl_packages', 'hbl.id', '=', 'hbl_packages.hbl_id')
                ->join('duplicate_container_hbl_package as dchp', 'hbl_packages.id', '=', 'dchp.hbl_package_id')
                ->join('containers', 'dchp.container_id', '=', 'containers.id')
                ->select([
                    'hbl.hbl_number',
                    'consignees.name as consignee_name',
                    'hbl.consignee_address as address',
                    'agent_from.name as agent_from_name',
                    'agent_to.name as agent_to_name',
                    'containers.container_number',
                    'containers.reference as cargo_manifest_no',
                    DB::raw('COALESCE(containers.unloading_ended_at, hbl.created_at) as destuffing_date'),
                    DB::raw('COALESCE(DATEDIFF(CURDATE(), containers.unloading_ended_at), DATEDIFF(CURDATE(), hbl.created_at)) as no_of_days'),
                    DB::raw('SUM(hbl_packages.quantity) as qty_manifest'),
                    DB::raw('SUM(hbl_packages.quantity) as qty_actual'),
                    DB::raw('GROUP_CONCAT(DISTINCT hbl_packages.package_type) as type_of_package')
                ])
                ->whereNull('hbl.deleted_at')
                ->where('containers.id', $this->filters['container_id'])
                ->groupBy([
                    'hbl.id',
                    'hbl.hbl_number',
                    'consignees.name',
                    'hbl.consignee_address',
                    'agent_from.name',
                    'agent_to.name',
                    'containers.container_number',
                    'containers.reference',
                    'containers.unloading_ended_at',
                    'hbl.created_at'
                ]);
        } else {
            // When no container filter, use simple HBL data
            $query = DB::table('hbl')
                ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                ->join('branches as agent_from', 'hbl.branch_id', '=', 'agent_from.id')
                ->leftJoin('branches as agent_to', 'hbl.warehouse_id', '=', 'agent_to.id')
                ->select([
                    'hbl.hbl_number',
                    'consignees.name as consignee_name',
                    'hbl.consignee_address as address',
                    'agent_from.name as agent_from_name',
                    'agent_to.name as agent_to_name',
                    DB::raw("'' as container_number"),
                    DB::raw("'' as cargo_manifest_no"),
                    DB::raw('hbl.created_at as destuffing_date'),
                    DB::raw('DATEDIFF(CURDATE(), hbl.created_at) as no_of_days'),
                    DB::raw('0 as qty_manifest'),
                    DB::raw('0 as qty_actual'),
                    DB::raw("'' as type_of_package")
                ])
                ->whereNull('hbl.deleted_at');
        }

        // Apply date range filters
        if (!empty($this->filters['date_from'])) {
            if (!empty($this->filters['container_id'])) {
                // When filtering by container, use container unloading date or HBL creation date
                $query->where(function($q) {
                    $q->whereDate('containers.unloading_ended_at', '>=', $this->filters['date_from'])
                      ->orWhere(function($subQ) {
                          $subQ->whereNull('containers.unloading_ended_at')
                               ->whereDate('hbl.created_at', '>=', $this->filters['date_from']);
                      });
                });
            } else {
                $query->whereDate('hbl.created_at', '>=', $this->filters['date_from']);
            }
        }

        if (!empty($this->filters['date_to'])) {
            if (!empty($this->filters['container_id'])) {
                // When filtering by container, use container unloading date or HBL creation date
                $query->where(function($q) {
                    $q->whereDate('containers.unloading_ended_at', '<=', $this->filters['date_to'])
                      ->orWhere(function($subQ) {
                          $subQ->whereNull('containers.unloading_ended_at')
                               ->whereDate('hbl.created_at', '<=', $this->filters['date_to']);
                      });
                });
            } else {
                $query->whereDate('hbl.created_at', '<=', $this->filters['date_to']);
            }
        }

        // Apply agent from filter
        if (!empty($this->filters['agent_from'])) {
            $query->where('hbl.branch_id', $this->filters['agent_from']);
        }

        // Apply agent to filter
        if (!empty($this->filters['agent_to'])) {
            $query->where('hbl.warehouse_id', $this->filters['agent_to']);
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('hbl.hbl_number', 'like', "%{$search}%")
                    ->orWhere('consignees.name', 'like', "%{$search}%");

                // Add container reference search if container data is available
                if (!empty($this->filters['container_id'])) {
                    $q->orWhere('containers.reference', 'like', "%{$search}%");
                }
            });
        }

        if (!empty($this->filters['container_id'])) {
            return $query->orderBy(DB::raw('COALESCE(containers.unloading_ended_at, hbl.created_at)'), 'desc');
        } else {
            return $query->orderBy('hbl.created_at', 'desc');
        }
    }

    public function map($row): array
    {
        return [
            $row->hbl_number,
            $row->consignee_name . "\n" . $row->address,
            $row->type_of_package ?: '',
            $row->qty_manifest ?: 0,
            $row->qty_actual ?: 0,
        ];
    }

    public function headings(): array
    {
        return [
            [now()->format('d/m/Y'), now()->format('H:i:s')],
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Age Analysis of Consignees ' . $this->dateRange],
            ['Agent from ' . $this->agentFromName . ' To ' . $this->agentToName],
            ['', '', '', '', ''],
            [],
            ['AGENT' , $this->agentFromName],
            ['CONTAINER NO' , ($this->containerInfo['number'] ?? '')],
            ['DESTUFFING DATE' , ($this->filters['destuffing_date'] ?? '')],
            ['NO OF DAYS' , ($this->filters['no_of_days'] ?? '')],
            [],
            [
                'HBL No',
                'Consignee\'s Name & Address',
                'Type of Package',
                'Qty (Mani)',
                'Qty (Actual)',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => false, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            4 => [
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            5 => [
                'font' => ['bold' => false, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ],
            12 => [
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
        return 'Age Analysis Consignee';
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
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A3:E3');
                $sheet->mergeCells('A4:E4');

                // Remove borders from header rows (1-12)
                for ($row = 1; $row <= 12; $row++) {
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
                $sheet->getColumnDimension('A')->setWidth(12); // HBL No
                $sheet->getColumnDimension('B')->setWidth(35); // Consignee Name & Address
                $sheet->getColumnDimension('C')->setWidth(15); // Type of Package
                $sheet->getColumnDimension('D')->setWidth(10); // Qty (Mani)
                $sheet->getColumnDimension('E')->setWidth(10); // Qty (Actual)

                // Set header row height
                $sheet->getRowDimension(13)->setRowHeight(30);

                // Get the last row with data
                $lastRow = $sheet->getHighestRow();

                if ($lastRow > 13) {
                    // Apply borders to data rows
                    $sheet->getStyle('A13:E' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'font' => ['size' => 8],
                    ]);

                    // Center align numeric columns
                    $sheet->getStyle('D14:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Wrap text for consignee column
                    $sheet->getStyle('B14:B' . $lastRow)->getAlignment()->setWrapText(true);
                }
            },
        ];
    }
}
