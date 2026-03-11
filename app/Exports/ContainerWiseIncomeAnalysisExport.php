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

class ContainerWiseIncomeAnalysisExport implements
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
        'cons' => 0,
        'pkgs' => 0,
        'cbm' => 0,
        'slpa' => 0,
        'handling' => 0,
        'bond' => 0,
        'demurr' => 0,
        'frt_chg' => 0,
        'doc_chg' => 0,
        'vat' => 0,
        'nbt_paid' => 0,
        'total' => 0,
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
        $query = DB::table('containers')
            ->join('branches', 'containers.branch_id', '=', 'branches.id')
            ->leftJoin('container_hbl_package', function($join) {
                $join->on('containers.id', '=', 'container_hbl_package.container_id')
                     ->where('container_hbl_package.status', '=', 'loaded');
            })
            ->leftJoin('hbl_packages', 'container_hbl_package.hbl_package_id', '=', 'hbl_packages.id')
            ->leftJoin('hbl', function($join) {
                $join->on('hbl_packages.hbl_id', '=', 'hbl.id')
                     ->whereNull('hbl.deleted_at');
            })
            ->leftJoin('cashier_hbl_payments', 'hbl.id', '=', 'cashier_hbl_payments.hbl_id')
            ->select([
                'containers.container_number',
                'containers.unloading_started_at as destuff_date',
                'branches.name as agent_name',
                DB::raw('COUNT(DISTINCT CASE WHEN hbl.consignee_id IS NOT NULL THEN hbl.consignee_id END) as no_of_cons'),
                DB::raw('COUNT(DISTINCT CASE WHEN hbl_packages.id IS NOT NULL THEN hbl_packages.id END) as no_of_pkgs'),
                DB::raw('COALESCE(SUM(CASE WHEN hbl_packages.volume IS NOT NULL THEN hbl_packages.volume ELSE 0 END), 0) as cbm'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_slpa_charge IS NOT NULL THEN cashier_hbl_payments.destination_slpa_charge ELSE 0 END), 0) as slpa'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_handling_charge IS NOT NULL THEN cashier_hbl_payments.destination_handling_charge ELSE 0 END), 0) as handling'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_bond_charge IS NOT NULL THEN cashier_hbl_payments.destination_bond_charge ELSE 0 END), 0) as bond'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_demurrage_charge IS NOT NULL THEN cashier_hbl_payments.destination_demurrage_charge ELSE 0 END), 0) as demurr'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.departure_freight_charge IS NOT NULL THEN cashier_hbl_payments.departure_freight_charge ELSE 0 END), 0) as frt_chg'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_do_charge IS NOT NULL THEN cashier_hbl_payments.destination_do_charge ELSE 0 END), 0) as doc_chg'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_1_tax IS NOT NULL THEN cashier_hbl_payments.destination_1_tax ELSE 0 END), 0) as vat'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_2_tax IS NOT NULL THEN cashier_hbl_payments.destination_2_tax ELSE 0 END), 0) as nbt_paid'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_1_total_with_tax IS NOT NULL THEN cashier_hbl_payments.destination_1_total_with_tax ELSE 0 END) + SUM(CASE WHEN cashier_hbl_payments.destination_2_total_with_tax IS NOT NULL THEN cashier_hbl_payments.destination_2_total_with_tax ELSE 0 END) + SUM(CASE WHEN cashier_hbl_payments.departure_grand_total IS NOT NULL THEN cashier_hbl_payments.departure_grand_total ELSE 0 END), 0) as total'),
            ])
            ->whereNotNull('containers.unloading_started_at')
            ->whereNull('containers.deleted_at')
            ->groupBy(
                'containers.id',
                'containers.container_number',
                'containers.unloading_started_at',
                'branches.name'
            )
            ->orderBy('containers.unloading_started_at', 'desc');

        // Apply date range filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('containers.unloading_started_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('containers.unloading_started_at', '<=', $this->filters['date_to']);
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('containers.container_number', 'like', "%{$search}%")
                    ->orWhere('branches.name', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function map($row): array
    {
        $this->rowCount++;
        
        // Accumulate totals
        $cons = (int) $row->no_of_cons;
        $pkgs = (int) $row->no_of_pkgs;
        $cbm = (float) ($row->cbm ?? 0);
        $slpa = (float) ($row->slpa ?? 0);
        $handling = (float) ($row->handling ?? 0);
        $bond = (float) ($row->bond ?? 0);
        $demurr = (float) ($row->demurr ?? 0);
        $frt_chg = (float) ($row->frt_chg ?? 0);
        $doc_chg = (float) ($row->doc_chg ?? 0);
        $vat = (float) ($row->vat ?? 0);
        $nbt_paid = (float) ($row->nbt_paid ?? 0);
        $total = (float) ($row->total ?? 0);
        
        $this->totals['cons'] += $cons;
        $this->totals['pkgs'] += $pkgs;
        $this->totals['cbm'] += $cbm;
        $this->totals['slpa'] += $slpa;
        $this->totals['handling'] += $handling;
        $this->totals['bond'] += $bond;
        $this->totals['demurr'] += $demurr;
        $this->totals['frt_chg'] += $frt_chg;
        $this->totals['doc_chg'] += $doc_chg;
        $this->totals['vat'] += $vat;
        $this->totals['nbt_paid'] += $nbt_paid;
        $this->totals['total'] += $total;

        return [
            $row->container_number,
            $row->destuff_date ? date('d/m/Y', strtotime($row->destuff_date)) : '',
            $row->agent_name,
            $cons,
            $pkgs,
            $cbm,
            $slpa,
            $handling,
            $bond,
            $demurr,
            $frt_chg,
            $doc_chg,
            $vat,
            $nbt_paid,
            $total,
        ];
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Container Wise Income Analysis ' . $this->dateRange],
            [date('d/m/Y') . '    ' . date('H:i:s')],
            ['PAGE -    1'],
            [],
            [
                'Container No',
                'Destuff',
                'Agent',
                'No of Cons',
                'No of Pkgs',
                'CBM',
                'SLPA',
                'Handling',
                'Bond',
                'Demurr',
                'Frt Chg',
                'DOC Chg',
                'VAT',
                'NBT Paid',
                'Total',
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
            4 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
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
        return 'Container Wise Income Analysis';
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
                $sheet->mergeCells('A1:O1');
                $sheet->mergeCells('A2:O2');

                // Remove borders from header rows (1-4)
                for ($row = 1; $row <= 4; $row++) {
                    for ($col = 'A'; $col <= 'O'; $col++) {
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
                $sheet->getColumnDimension('A')->setWidth(12); // Container No
                $sheet->getColumnDimension('B')->setWidth(10); // Destuff
                $sheet->getColumnDimension('C')->setWidth(10); // Agent
                $sheet->getColumnDimension('D')->setWidth(8);  // No of Cons
                $sheet->getColumnDimension('E')->setWidth(8);  // No of Pkgs
                $sheet->getColumnDimension('F')->setWidth(8);  // CBM
                $sheet->getColumnDimension('G')->setWidth(9);  // SLPA
                $sheet->getColumnDimension('H')->setWidth(9);  // Handling
                $sheet->getColumnDimension('I')->setWidth(8);  // Bond
                $sheet->getColumnDimension('J')->setWidth(8);  // Demurr
                $sheet->getColumnDimension('K')->setWidth(9);  // Frt Chg
                $sheet->getColumnDimension('L')->setWidth(9);  // DOC Chg
                $sheet->getColumnDimension('M')->setWidth(8);  // VAT
                $sheet->getColumnDimension('N')->setWidth(9);  // NBT Paid
                $sheet->getColumnDimension('O')->setWidth(10); // Total

                // Apply borders to data rows (starting from row 6)
                if ($this->rowCount > 0) {
                    $lastRow = 6 + $this->rowCount;
                    
                    // Explicitly set cell values as numeric to ensure 0 displays
                    for ($row = 7; $row <= $lastRow; $row++) {
                        // Get current values and set as explicit numeric values
                        for ($col = 'D'; $col <= 'O'; $col++) {
                            $value = $sheet->getCell("{$col}{$row}")->getValue();
                            $sheet->setCellValueExplicit("{$col}{$row}", $value ?? 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                        }
                    }
                    
                    $sheet->getStyle('A6:O' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    // Format numeric columns
                    $sheet->getStyle('D7:E' . $lastRow)->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle('F7:F' . $lastRow)->getNumberFormat()->setFormatCode('0.000');
                    $sheet->getStyle('G7:O' . $lastRow)->getNumberFormat()->setFormatCode('0.00');
                    
                    // Right align numeric columns
                    $sheet->getStyle('D7:O' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    
                    // Add Grand Total footer row
                    $footerRow = $lastRow + 1;
                    $sheet->setCellValue("A{$footerRow}", "Grand Total");
                    $sheet->setCellValue("B{$footerRow}", "");
                    $sheet->setCellValue("C{$footerRow}", "");
                    $sheet->setCellValueExplicit("D{$footerRow}", $this->totals['cons'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("E{$footerRow}", $this->totals['pkgs'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("F{$footerRow}", $this->totals['cbm'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("G{$footerRow}", $this->totals['slpa'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("H{$footerRow}", $this->totals['handling'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("I{$footerRow}", $this->totals['bond'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("J{$footerRow}", $this->totals['demurr'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("K{$footerRow}", $this->totals['frt_chg'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("L{$footerRow}", $this->totals['doc_chg'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("M{$footerRow}", $this->totals['vat'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("N{$footerRow}", $this->totals['nbt_paid'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("O{$footerRow}", $this->totals['total'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    
                    // Style footer row
                    $sheet->getStyle("A{$footerRow}:O{$footerRow}")->applyFromArray([
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
                    $sheet->getStyle("D{$footerRow}:E{$footerRow}")->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle("F{$footerRow}")->getNumberFormat()->setFormatCode('0.000');
                    $sheet->getStyle("G{$footerRow}:O{$footerRow}")->getNumberFormat()->setFormatCode('0.00');
                    
                    // Right align footer numeric columns
                    $sheet->getStyle("D{$footerRow}:O{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }
}