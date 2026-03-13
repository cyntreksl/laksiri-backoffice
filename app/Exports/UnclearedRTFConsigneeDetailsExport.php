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

class UnclearedRTFConsigneeDetailsExport implements
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
        'pkgs' => 0,
        'cbm' => 0,
    ];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        
        $dateFrom = !empty($filters['date_from']) ? date('d/m/Y', strtotime($filters['date_from'])) : '';
        $dateTo = !empty($filters['date_to']) ? date('d/m/Y', strtotime($filters['date_to'])) : '';
        
        if ($dateFrom && $dateTo) {
            $this->dateRange = "From {$dateFrom} TO {$dateTo}";
        } else {
            $this->dateRange = "All Records";
        }
    }

    public function query()
    {
        $query = DB::table('hbl')
            ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
            ->join('branches', 'hbl.branch_id', '=', 'branches.id')
            ->leftJoin('hbl_packages', function($join) {
                $join->on('hbl.id', '=', 'hbl_packages.hbl_id')
                     ->whereNull('hbl_packages.deleted_at');
            })
            ->leftJoin('detain_records', function($join) {
                $join->on('hbl_packages.id', '=', 'detain_records.rtfable_id')
                     ->where('detain_records.rtfable_type', '=', 'App\\Models\\HBLPackage')
                     ->where('detain_records.is_rtf', '=', true)
                     ->whereNull('detain_records.lifted_at'); // Only active RTF records
            })
            ->select([
                'hbl.hbl_number',
                'consignees.name as consignee_name',
                'branches.name as agent_name',
                'hbl.created_at as date',
                DB::raw('COUNT(DISTINCT CASE WHEN hbl_packages.id IS NOT NULL THEN hbl_packages.id END) as no_of_pkgs'),
                DB::raw('COALESCE(SUM(CASE WHEN hbl_packages.volume IS NOT NULL THEN hbl_packages.volume ELSE 0 END), 0) as cbm'),
            ])
            ->whereNull('hbl.deleted_at')
            ->whereNotNull('detain_records.id') // Must have RTF detain record
            ->where(function($query) {
                $query->where('hbl.status', '!=', 'completed')
                      ->orWhereNull('hbl.status');
            }) // Uncleared means not completed
            ->groupBy(
                'hbl.id',
                'hbl.hbl_number',
                'consignees.name',
                'branches.name',
                'hbl.created_at'
            )
            ->orderBy('hbl.created_at', 'desc');

        // Apply date range filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('hbl.created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('hbl.created_at', '<=', $this->filters['date_to']);
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('hbl.hbl_number', 'like', "%{$search}%")
                    ->orWhere('consignees.name', 'like', "%{$search}%")
                    ->orWhere('branches.name', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function map($row): array
    {
        $this->rowCount++;
        
        // Accumulate totals
        $pkgs = (int) ($row->no_of_pkgs ?? 0);
        $cbm = (float) ($row->cbm ?? 0);
        
        $this->totals['pkgs'] += $pkgs;
        $this->totals['cbm'] += $cbm;

        return [
            $this->rowCount, // Serial No
            $row->hbl_number,
            $row->consignee_name,
            $pkgs,
            $cbm,
            $row->date ? date('d/m/Y', strtotime($row->date)) : '',
            $row->agent_name,
        ];
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Uncleared RTF Consignee Details'],
            [$this->dateRange],
            [date('d/m/Y') . '  ' . date('H:i:s'), '', '', '', '', '', 'PAGE - 1'],
            [],
            [
                'Serial No',
                'HBL No',
                'Consignee Name',
                'No of Pkgs',
                'CBM',
                'Date',
                'Agent',
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
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            4 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            6 => [
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
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'Uncleared RTF Consignee Details';
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

                // Set header and footer for page numbers
                $sheet->getHeaderFooter()
                    ->setOddFooter('&RPAGE - &P');

                // Merge title cells
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');
                $sheet->mergeCells('A3:G3');

                // Remove borders from header rows (1-4)
                for ($row = 1; $row <= 4; $row++) {
                    for ($col = 'A'; $col <= 'G'; $col++) {
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
                $sheet->getColumnDimension('A')->setWidth(10); // Serial No
                $sheet->getColumnDimension('B')->setWidth(12); // HBL No
                $sheet->getColumnDimension('C')->setWidth(25); // Consignee Name
                $sheet->getColumnDimension('D')->setWidth(10); // No of Pkgs
                $sheet->getColumnDimension('E')->setWidth(10); // CBM
                $sheet->getColumnDimension('F')->setWidth(12); // Date
                $sheet->getColumnDimension('G')->setWidth(20); // Agent

                // Apply borders to data rows (starting from row 6)
                if ($this->rowCount > 0) {
                    $lastRow = 6 + $this->rowCount;
                    
                    // Explicitly set cell values as numeric to ensure 0 displays
                    for ($row = 7; $row <= $lastRow; $row++) {
                        // Set numeric columns as explicit numeric values
                        $pkgsValue = $sheet->getCell("D{$row}")->getValue();
                        $cbmValue = $sheet->getCell("E{$row}")->getValue();
                        
                        $sheet->setCellValueExplicit("D{$row}", $pkgsValue ?? 0, DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicit("E{$row}", $cbmValue ?? 0, DataType::TYPE_NUMERIC);
                    }
                    
                    $sheet->getStyle('A6:G' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    // Format numeric columns
                    $sheet->getStyle('A7:A' . $lastRow)->getNumberFormat()->setFormatCode('0'); // Serial No
                    $sheet->getStyle('D7:D' . $lastRow)->getNumberFormat()->setFormatCode('0'); // No of Pkgs
                    $sheet->getStyle('E7:E' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.000'); // CBM
                    
                    // Right align numeric columns
                    $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('D7:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle('E7:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    
                    // Center align date column
                    $sheet->getStyle('F7:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    // Add Total footer row
                    $footerRow = $lastRow + 1;
                    $sheet->setCellValue("A{$footerRow}", "");
                    $sheet->setCellValue("B{$footerRow}", "");
                    $sheet->setCellValue("C{$footerRow}", "Total");
                    $sheet->setCellValueExplicit("D{$footerRow}", $this->totals['pkgs'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("E{$footerRow}", $this->totals['cbm'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValue("F{$footerRow}", "");
                    $sheet->setCellValue("G{$footerRow}", "");
                    
                    // Style footer row
                    $sheet->getStyle("A{$footerRow}:G{$footerRow}")->applyFromArray([
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
                    $sheet->getStyle("D{$footerRow}")->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle("E{$footerRow}")->getNumberFormat()->setFormatCode('#,##0.000');
                    
                    // Right align footer numeric columns
                    $sheet->getStyle("D{$footerRow}:E{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }
}