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

class AgentWiseIncomeAnalysisExport implements
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
        'discount' => 0,
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
        $query = DB::table('branches')
            ->leftJoin('hbl', function($join) {
                $join->on('branches.id', '=', 'hbl.branch_id')
                     ->whereNull('hbl.deleted_at');
            })
            ->leftJoin('hbl_packages', function($join) {
                $join->on('hbl.id', '=', 'hbl_packages.hbl_id')
                     ->whereNull('hbl_packages.deleted_at');
            })
            ->leftJoin('cashier_hbl_payments', 'hbl.id', '=', 'cashier_hbl_payments.hbl_id')
            ->select([
                'branches.name as agent_name',
                DB::raw('COUNT(DISTINCT CASE WHEN hbl.consignee_id IS NOT NULL THEN hbl.consignee_id END) as no_of_cons'),
                DB::raw('COUNT(DISTINCT CASE WHEN hbl_packages.id IS NOT NULL THEN hbl_packages.id END) as no_of_pkgs'),
                DB::raw('COALESCE(SUM(CASE WHEN hbl_packages.volume IS NOT NULL THEN hbl_packages.volume ELSE 0 END), 0) as cbm'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_slpa_charge IS NOT NULL THEN cashier_hbl_payments.destination_slpa_charge ELSE 0 END), 0) as slpa'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_handling_charge IS NOT NULL THEN cashier_hbl_payments.destination_handling_charge ELSE 0 END), 0) as handling'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_bond_charge IS NOT NULL THEN cashier_hbl_payments.destination_bond_charge ELSE 0 END), 0) as bond'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_demurrage_charge IS NOT NULL THEN cashier_hbl_payments.destination_demurrage_charge ELSE 0 END), 0) as demurr'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.discount IS NOT NULL THEN cashier_hbl_payments.discount ELSE 0 END), 0) as discount'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_1_tax IS NOT NULL THEN cashier_hbl_payments.destination_1_tax ELSE 0 END), 0) as vat'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_2_tax IS NOT NULL THEN cashier_hbl_payments.destination_2_tax ELSE 0 END), 0) as nbt_paid'),
                DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_1_total_with_tax IS NOT NULL THEN cashier_hbl_payments.destination_1_total_with_tax ELSE 0 END) + SUM(CASE WHEN cashier_hbl_payments.destination_2_total_with_tax IS NOT NULL THEN cashier_hbl_payments.destination_2_total_with_tax ELSE 0 END) + SUM(CASE WHEN cashier_hbl_payments.departure_grand_total IS NOT NULL THEN cashier_hbl_payments.departure_grand_total ELSE 0 END), 0) as total'),
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
        $cons = (int) ($row->no_of_cons ?? 0);
        $pkgs = (int) ($row->no_of_pkgs ?? 0);
        $cbm = (float) ($row->cbm ?? 0);
        $slpa = (float) ($row->slpa ?? 0);
        $handling = (float) ($row->handling ?? 0);
        $bond = (float) ($row->bond ?? 0);
        $demurr = (float) ($row->demurr ?? 0);
        $discount = (float) ($row->discount ?? 0);
        $vat = (float) ($row->vat ?? 0);
//        $nbt_paid = (float) ($row->nbt_paid ?? 0);
        $nbt_paid = (float) (0);
        $total = (float) ($row->total ?? 0);

        $this->totals['cons'] += $cons;
        $this->totals['pkgs'] += $pkgs;
        $this->totals['cbm'] += $cbm;
        $this->totals['slpa'] += $slpa;
        $this->totals['handling'] += $handling;
        $this->totals['bond'] += $bond;
        $this->totals['demurr'] += $demurr;
        $this->totals['discount'] += $discount;
        $this->totals['vat'] += $vat;
        $this->totals['nbt_paid'] += $nbt_paid;
        $this->totals['total'] += $total;

        return [
            $row->agent_name,
            $cons,
            $pkgs,
            $cbm,
            $slpa,
            $handling,
            $bond,
            $demurr,
            $discount,
            $vat,
            $total,
            $nbt_paid,
        ];
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Agent Wise Income Analysis ' . $this->dateRange],
            [date('d/m/Y') . '  ' . date('H:i:s'), '', '', '', '', '', '', '', '', '', 'PAGE - 1'],
            [],
            [
                'Agent Name',
                'No of Cons',
                'No of Pkgs',
                'CBM',
                'SLPA',
                'Handling',
                'Bond',
                'Demurr',
                'Discount',
                'VAT',
                'Total',
                'NBT Paid',
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
        return 'Agent Wise Income Analysis';
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
                $sheet->mergeCells('A1:L1');
                $sheet->mergeCells('A2:L2');

                // Remove borders from header rows (1-3)
                for ($row = 1; $row <= 3; $row++) {
                    for ($col = 'A'; $col <= 'L'; $col++) {
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

                // Set column widths - adjusted to fit all columns on A4 landscape
                $sheet->getColumnDimension('A')->setWidth(25); // Agent Name
                $sheet->getColumnDimension('B')->setWidth(8);  // No of Cons
                $sheet->getColumnDimension('C')->setWidth(8);  // No of Pkgs
                $sheet->getColumnDimension('D')->setWidth(8);  // CBM
                $sheet->getColumnDimension('E')->setWidth(10); // SLPA
                $sheet->getColumnDimension('F')->setWidth(10); // Handling
                $sheet->getColumnDimension('G')->setWidth(10); // Bond
                $sheet->getColumnDimension('H')->setWidth(10); // Demurr
                $sheet->getColumnDimension('I')->setWidth(10); // Discount
                $sheet->getColumnDimension('J')->setWidth(10); // VAT
                $sheet->getColumnDimension('K')->setWidth(12); // Total
                $sheet->getColumnDimension('L')->setWidth(10); // NBT Paid

                // Set header row height to accommodate text
                $sheet->getRowDimension(5)->setRowHeight(30);

                // Apply borders to data rows (starting from row 5)
                if ($this->rowCount > 0) {
                    $lastRow = 5 + $this->rowCount;

                    // Explicitly set cell values as numeric to ensure 0 displays
                    for ($row = 6; $row <= $lastRow; $row++) {
                        for ($col = 'B'; $col <= 'L'; $col++) {
                            $value = $sheet->getCell("{$col}{$row}")->getValue();
                            $sheet->setCellValueExplicit("{$col}{$row}", $value ?? 0, DataType::TYPE_NUMERIC);
                        }
                    }

                    $sheet->getStyle('A5:L' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'font' => ['size' => 9],
                    ]);

                    // Format numeric columns
                    $sheet->getStyle('B6:C' . $lastRow)->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle('D6:L' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');

                    // Right align numeric columns
                    $sheet->getStyle('B6:L' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Add Grand Total footer row
                    $footerRow = $lastRow + 1;
                    $sheet->setCellValue("A{$footerRow}", "Grand Total");
                    $sheet->setCellValueExplicit("B{$footerRow}", $this->totals['cons'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("C{$footerRow}", $this->totals['pkgs'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("D{$footerRow}", $this->totals['cbm'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("E{$footerRow}", $this->totals['slpa'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("F{$footerRow}", $this->totals['handling'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("G{$footerRow}", $this->totals['bond'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("H{$footerRow}", $this->totals['demurr'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("I{$footerRow}", $this->totals['discount'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("J{$footerRow}", $this->totals['vat'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("K{$footerRow}", $this->totals['total'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("L{$footerRow}", $this->totals['nbt_paid'], DataType::TYPE_NUMERIC);

                    // Style footer row
                    $sheet->getStyle("A{$footerRow}:L{$footerRow}")->applyFromArray([
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
                    $sheet->getStyle("B{$footerRow}:C{$footerRow}")->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle("D{$footerRow}:L{$footerRow}")->getNumberFormat()->setFormatCode('#,##0.00');

                    // Right align footer numeric columns
                    $sheet->getStyle("B{$footerRow}:L{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }
}
