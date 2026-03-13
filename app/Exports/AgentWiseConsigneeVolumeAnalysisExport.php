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
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class AgentWiseConsigneeVolumeAnalysisExport implements
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
    protected $rowCount = 0;
    protected $totals = [
        'consignees' => 0,
        'pkgs_manifest' => 0,
        'pkgs_actual' => 0,
        'cbm' => 0,
    ];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        
        $dateFrom = !empty($filters['date_from']) ? date('d/m/Y', strtotime($filters['date_from'])) : '';
        $dateTo = !empty($filters['date_to']) ? date('d/m/Y', strtotime($filters['date_to'])) : '';
        
        if ($dateFrom && $dateTo) {
            $this->dateRange = "From {$dateFrom} To {$dateTo}";
        } else {
            $this->dateRange = "All Records";
        }
    }

    public function query()
    {
        $query = DB::table('branches')
            ->leftJoin('hbl', function($join) {
                $join->on('branches.id', '=', 'hbl.branch_id')
                     ->whereNull('hbl.deleted_at');
            })
            ->leftJoin('hbl_packages', function($join) {
                $join->on('hbl.id', '=', 'hbl_packages.hbl_id')
                     ->whereNull('hbl_packages.deleted_at');
            })
            ->select([
                'branches.name as agent_name',
                DB::raw('COUNT(DISTINCT CASE WHEN hbl.consignee_id IS NOT NULL THEN hbl.consignee_id END) as no_of_consignees'),
                DB::raw('COUNT(DISTINCT CASE WHEN hbl_packages.id IS NOT NULL THEN hbl_packages.id END) as no_of_pkgs_manifest'),
                DB::raw('COUNT(DISTINCT CASE WHEN hbl_packages.id IS NOT NULL THEN hbl_packages.id END) as no_of_pkgs_actual'),
                DB::raw('COALESCE(SUM(CASE WHEN hbl_packages.volume IS NOT NULL THEN hbl_packages.volume ELSE 0 END), 0) as cbm'),
            ])
            ->whereNull('branches.deleted_at')
            ->groupBy('branches.id', 'branches.name')
            ->orderBy('branches.name', 'asc');

        // Apply date range filters
        if (!empty($this->filters['date_from'])) {
            $query->where(function($q) {
                $q->whereDate('hbl.created_at', '>=', $this->filters['date_from'])
                  ->orWhereNull('hbl.created_at');
            });
        }

        if (!empty($this->filters['date_to'])) {
            $query->where(function($q) {
                $q->whereDate('hbl.created_at', '<=', $this->filters['date_to'])
                  ->orWhereNull('hbl.created_at');
            });
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where('branches.name', 'like', "%{$search}%");
        }

        return $query;
    }

    public function map($row): array
    {
        $this->rowCount++;
        
        // Accumulate totals
        $consignees = (int) ($row->no_of_consignees ?? 0);
        $pkgs_manifest = (int) ($row->no_of_pkgs_manifest ?? 0);
        $pkgs_actual = (int) ($row->no_of_pkgs_actual ?? 0);
        $cbm = (float) ($row->cbm ?? 0);
        
        $this->totals['consignees'] += $consignees;
        $this->totals['pkgs_manifest'] += $pkgs_manifest;
        $this->totals['pkgs_actual'] += $pkgs_actual;
        $this->totals['cbm'] += $cbm;

        return [
            $row->agent_name,
            $consignees,
            $pkgs_manifest,
            $pkgs_actual,
            $cbm,
        ];
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Agent Wise Total Incoming Consignee & Volume Of Cargo Analysis'],
            [date('d/m/Y') . '  ' . date('H:i:s'), $this->dateRange, '', '', 'PAGE - 1'],
            [],
            [
                'Agent Name',
                'No of Consignees',
                'No of PKgs (Manifest)',
                'No of PKgs (Actual)',
                'CBM',
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
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            5 => [
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
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Agent Wise Consignee Volume';
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
                    ->setOddFooter('&RPAGE - &P');

                // Merge title cells
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');

                // Remove borders from header rows (1-3)
                for ($row = 1; $row <= 3; $row++) {
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
                $sheet->getColumnDimension('A')->setWidth(40); // Agent Name
                $sheet->getColumnDimension('B')->setWidth(18); // No of Consignees
                $sheet->getColumnDimension('C')->setWidth(20); // No of PKgs (Manifest)
                $sheet->getColumnDimension('D')->setWidth(20); // No of PKgs (Actual)
                $sheet->getColumnDimension('E')->setWidth(15); // CBM

                // Set header row height to accommodate text
                $sheet->getRowDimension(5)->setRowHeight(30);

                // Apply borders to data rows (starting from row 5)
                if ($this->rowCount > 0) {
                    $lastRow = 5 + $this->rowCount;
                    
                    // Explicitly set cell values as numeric to ensure 0 displays
                    for ($row = 6; $row <= $lastRow; $row++) {
                        for ($col = 'B'; $col <= 'E'; $col++) {
                            $value = $sheet->getCell("{$col}{$row}")->getValue();
                            $sheet->setCellValueExplicit("{$col}{$row}", $value ?? 0, DataType::TYPE_NUMERIC);
                        }
                    }
                    
                    $sheet->getStyle('A5:E' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    // Format numeric columns
                    $sheet->getStyle('B6:D' . $lastRow)->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle('E6:E' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
                    
                    // Right align numeric columns
                    $sheet->getStyle('B6:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    
                    // Add Grand Total footer row
                    $footerRow = $lastRow + 1;
                    $sheet->setCellValue("A{$footerRow}", "Grand Total");
                    $sheet->setCellValueExplicit("B{$footerRow}", $this->totals['consignees'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("C{$footerRow}", $this->totals['pkgs_manifest'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("D{$footerRow}", $this->totals['pkgs_actual'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("E{$footerRow}", $this->totals['cbm'], DataType::TYPE_NUMERIC);
                    
                    // Style footer row
                    $sheet->getStyle("A{$footerRow}:E{$footerRow}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E5E7EB'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                    
                    // Apply number formats to footer
                    $sheet->getStyle("B{$footerRow}:D{$footerRow}")->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle("E{$footerRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    
                    // Right align footer numeric columns
                    $sheet->getStyle("B{$footerRow}:E{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }
}
