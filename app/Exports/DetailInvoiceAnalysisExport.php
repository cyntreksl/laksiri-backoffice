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
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DetailInvoiceAnalysisExport implements
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
        'packages' => 0,
        'cbm' => 0,
        'slpa' => 0,
        'handling' => 0,
        'bond' => 0,
        'demurrage' => 0,
        'vat' => 0,
        'discount' => 0,
        'total' => 0,
    ];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;

        $dateFrom = !empty($filters['date_from']) ? date('d/m/Y', strtotime($filters['date_from'])) : '';
        $dateTo = !empty($filters['date_to']) ? date('d/m/Y', strtotime($filters['date_to'])) : '';

        if ($dateFrom && $dateTo) {
            $this->dateRange = "From Date {$dateFrom} To {$dateTo}";
        } else {
            $this->dateRange = "All Records";
        }
    }

    public function query()
    {
        $query = DB::table('cashier_hbl_payments')
            ->join('hbl', 'cashier_hbl_payments.hbl_id', '=', 'hbl.id')
            ->join('hbl_packages', 'hbl.id', '=', 'hbl_packages.hbl_id')
            ->select([
                'cashier_hbl_payments.invoice_number',
                'hbl.hbl_number',
                DB::raw('COUNT(hbl_packages.id) as no_of_pkgs'),
                DB::raw('COALESCE(SUM(hbl_packages.volume), 0) as cbm'),
                DB::raw('COALESCE(cashier_hbl_payments.destination_slpa_charge, 0) as destination_slpa_charge'),
                DB::raw('COALESCE(cashier_hbl_payments.destination_handling_charge, 0) as destination_handling_charge'),
                DB::raw('COALESCE(cashier_hbl_payments.destination_bond_charge, 0) as destination_bond_charge'),
                DB::raw('COALESCE(cashier_hbl_payments.destination_demurrage_charge, 0) as destination_demurrage_charge'),
                DB::raw('COALESCE(cashier_hbl_payments.destination_1_tax, 0) as vat'),
                DB::raw('COALESCE(cashier_hbl_payments.discount, 0) as discount'),
                DB::raw('(COALESCE(cashier_hbl_payments.destination_1_total_with_tax, 0) + COALESCE(cashier_hbl_payments.destination_2_total_with_tax, 0) + COALESCE(cashier_hbl_payments.departure_grand_total, 0)) as total'),
            ])
            ->whereNotNull('cashier_hbl_payments.invoice_number')
            ->whereNull('hbl.deleted_at')
            ->whereNull('hbl_packages.deleted_at')
            ->groupBy(
                'cashier_hbl_payments.id',
                'cashier_hbl_payments.invoice_number',
                'hbl.hbl_number',
                'cashier_hbl_payments.destination_slpa_charge',
                'cashier_hbl_payments.destination_handling_charge',
                'cashier_hbl_payments.destination_bond_charge',
                'cashier_hbl_payments.destination_demurrage_charge',
                'cashier_hbl_payments.destination_1_tax',
                'cashier_hbl_payments.discount',
                'cashier_hbl_payments.destination_1_total_with_tax',
                'cashier_hbl_payments.destination_2_total_with_tax',
                'cashier_hbl_payments.departure_grand_total',
                'cashier_hbl_payments.created_at'
            )
            ->orderBy('cashier_hbl_payments.created_at', 'asc');

        // Apply date range filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('cashier_hbl_payments.created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('cashier_hbl_payments.created_at', '<=', $this->filters['date_to']);
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('cashier_hbl_payments.invoice_number', 'like', "%{$search}%")
                    ->orWhere('hbl.hbl_number', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function map($row): array
    {
        $this->rowCount++;

        // Accumulate totals
        $packages = (int) $row->no_of_pkgs;
        $cbm = (float) ($row->cbm ?? 0);
        $slpa = (float) ($row->destination_slpa_charge ?? 0);
        $handling = (float) ($row->destination_handling_charge ?? 0);
        $bond = (float) ($row->destination_bond_charge ?? 0);
        $demurrage = (float) ($row->destination_demurrage_charge ?? 0);
        $vat = (float) ($row->vat ?? 0);
        $discount = (float) ($row->discount ?? 0);
        $total = (float) ($row->total ?? 0);

        $this->totals['packages'] += $packages;
        $this->totals['cbm'] += $cbm;
        $this->totals['slpa'] += $slpa;
        $this->totals['handling'] += $handling;
        $this->totals['bond'] += $bond;
        $this->totals['demurrage'] += $demurrage;
        $this->totals['vat'] += $vat;
        $this->totals['discount'] += $discount;
        $this->totals['total'] += $total;

        return [
            $row->invoice_number,
            $row->hbl_number,
            $packages,
            $cbm,
            $slpa,
            $handling,
            $bond,
            $demurrage,
            $vat,
            $discount,
            $total,
        ];
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['NO.31,ST. ANTHONY\'S MAWATHA Colombo 03 Sri lanka'],
            ['Detail Invoice Analysis'],
            [$this->dateRange],
            [now()->format('d/m/Y h:i:s A')],
            [],
            [
                'Invoice No.',
                'HBL',
                'No.of Pkgs.',
                'CBM',
                'SLPA',
                'Handling',
                'Bond',
                'Demu.',
                'VAT',
                'Discount',
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
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            4 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            5 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            7 => [
                'font' => ['bold' => true],
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
        return 'Detail Invoice Analysis';
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
                $sheet->mergeCells('A1:K1');
                $sheet->mergeCells('A2:K2');
                $sheet->mergeCells('A3:K3');
                $sheet->mergeCells('A4:K4');
                $sheet->mergeCells('A5:K5');

                // Remove borders from header rows (1-5)
                for ($row = 1; $row <= 5; $row++) {
                    for ($col = 'A'; $col <= 'K'; $col++) {
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
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(10);
                $sheet->getColumnDimension('E')->setWidth(12);
                $sheet->getColumnDimension('F')->setWidth(12);
                $sheet->getColumnDimension('G')->setWidth(12);
                $sheet->getColumnDimension('H')->setWidth(12);
                $sheet->getColumnDimension('I')->setWidth(12);
                $sheet->getColumnDimension('J')->setWidth(12);
                $sheet->getColumnDimension('K')->setWidth(12);

                // Apply borders to data rows (starting from row 7)
                if ($this->rowCount > 0) {
                    $lastRow = 7 + $this->rowCount;

                    // Explicitly set cell values as numeric to ensure 0 displays
                    for ($row = 8; $row <= $lastRow; $row++) {
                        // Get current values
                        $cbm = $sheet->getCell("D{$row}")->getValue();
                        $slpa = $sheet->getCell("E{$row}")->getValue();
                        $handling = $sheet->getCell("F{$row}")->getValue();
                        $bond = $sheet->getCell("G{$row}")->getValue();
                        $demu = $sheet->getCell("H{$row}")->getValue();
                        $vat = $sheet->getCell("I{$row}")->getValue();
                        $discount = $sheet->getCell("J{$row}")->getValue();
                        $total = $sheet->getCell("K{$row}")->getValue();

                        // Set as explicit numeric values
                        $sheet->setCellValueExplicit("D{$row}", $cbm ?? 0, DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicit("E{$row}", $slpa ?? 0, DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicit("F{$row}", $handling ?? 0, DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicit("G{$row}", $bond ?? 0, DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicit("H{$row}", $demu ?? 0, DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicit("I{$row}", $vat ?? 0, DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicit("J{$row}", $discount ?? 0, DataType::TYPE_NUMERIC);
                        $sheet->setCellValueExplicit("K{$row}", $total ?? 0, DataType::TYPE_NUMERIC);
                    }

                    $sheet->getStyle('A7:K' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    // Format numeric columns with 2 decimal places
                    $sheet->getStyle('C8:C' . $lastRow)->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle('D8:D' . $lastRow)->getNumberFormat()->setFormatCode('0.000');
                    $sheet->getStyle('E8:K' . $lastRow)->getNumberFormat()->setFormatCode('0.00');

                    // Right align numeric columns
                    $sheet->getStyle('C8:K' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Add Grand Total footer row
                    $footerRow = $lastRow + 1;
                    $sheet->setCellValue("A{$footerRow}", "Total");
                    $sheet->setCellValue("B{$footerRow}", "");
                    $sheet->setCellValueExplicit("C{$footerRow}", $this->totals['packages'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("D{$footerRow}", $this->totals['cbm'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("E{$footerRow}", $this->totals['slpa'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("F{$footerRow}", $this->totals['handling'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("G{$footerRow}", $this->totals['bond'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("H{$footerRow}", $this->totals['demurrage'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("I{$footerRow}", $this->totals['vat'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("J{$footerRow}", $this->totals['discount'], DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("K{$footerRow}", $this->totals['total'], DataType::TYPE_NUMERIC);

                    // Style footer row
                    $sheet->getStyle("A{$footerRow}:K{$footerRow}")->applyFromArray([
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
                    $sheet->getStyle("C{$footerRow}")->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle("D{$footerRow}")->getNumberFormat()->setFormatCode('0.000');
                    $sheet->getStyle("E{$footerRow}:K{$footerRow}")->getNumberFormat()->setFormatCode('0.00');

                    // Right align footer numeric columns
                    $sheet->getStyle("C{$footerRow}:K{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }
}
