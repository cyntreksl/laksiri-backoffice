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

class StampDutyReportExport implements
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
    protected $cargoType;
    protected $rowCount = 0;

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

        $this->cargoType = !empty($filters['cargo_type']) ? $filters['cargo_type'] : 'All';
    }

    public function query()
    {
        $query = DB::table('cashier_hbl_payments')
            ->join('hbl', 'cashier_hbl_payments.hbl_id', '=', 'hbl.id')
            ->select([
                'cashier_hbl_payments.created_at',
                'cashier_hbl_payments.invoice_number',
                'cashier_hbl_payments.destination_stamp_charge',
            ])
            ->whereNotNull('cashier_hbl_payments.invoice_number')
            ->whereNotNull('cashier_hbl_payments.destination_stamp_charge')
            ->where('cashier_hbl_payments.destination_stamp_charge', '>', 0)
            ->whereNull('hbl.deleted_at')
            ->orderBy('cashier_hbl_payments.created_at', 'asc');

        // Apply date range filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('cashier_hbl_payments.created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('cashier_hbl_payments.created_at', '<=', $this->filters['date_to']);
        }

        // Apply cargo type filter
        if (!empty($this->filters['cargo_type'])) {
            $query->where('hbl.cargo_type', $this->filters['cargo_type']);
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where('cashier_hbl_payments.invoice_number', 'like', "%{$search}%");
        }

        return $query;
    }

    public function map($row): array
    {
        $stampCharge = (float) ($row->destination_stamp_charge ?? 0);
        $this->rowCount++;

        return [
            date('d/m/Y', strtotime($row->created_at)),
            $row->invoice_number,
            $stampCharge, // Return as number, not formatted string
        ];
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['NO.31,ST. ANTHONY\'S MAWATHA Colombo 03 Sri lanka'],
            [],
            ['Stamp Duty - ' . $this->cargoType],
            [$this->dateRange],
            [now()->format('d/m/Y h:i:s A')],
            [],
            [
                'Date',
                'Invoice No.',
                'Amount',
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
            4 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            5 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            6 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            8 => [
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
        return 'Stamp Duty';
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

                // Set page setup for A4 size
                $sheet->getPageSetup()
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);

                // Set header and footer for page numbers
                $sheet->getHeaderFooter()
                    ->setOddFooter('&RPAGE - &P');

                // Merge title cells (header rows without borders)
                $sheet->mergeCells('A1:C1');
                $sheet->mergeCells('A2:C2');
                $sheet->mergeCells('A4:C4');
                $sheet->mergeCells('A5:C5');
                $sheet->mergeCells('A6:C6');

                // Remove borders from header rows (1-6)
                for ($row = 1; $row <= 6; $row++) {
                    for ($col = 'A'; $col <= 'C'; $col++) {
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
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(15);

                // Apply borders to data rows (starting from row 8)
                if ($this->rowCount > 0) {
                    $lastRow = 8 + $this->rowCount;
                    $sheet->getStyle('A8:C' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    // Format amount column with 2 decimal places and right align
                    $sheet->getStyle('C9:C' . $lastRow)->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                    $sheet->getStyle('C9:C' . $lastRow)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }
}
