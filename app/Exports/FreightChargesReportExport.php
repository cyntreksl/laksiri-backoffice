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

class FreightChargesReportExport implements
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
    protected $invoiceNumbers = [];
    protected $grandTotal = 0;
    protected $rowCount = 0;

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

        // Pre-load invoice numbers in chunks to avoid memory issues
        $this->loadInvoiceNumbers();
    }

    protected function loadInvoiceNumbers()
    {
        // Load invoice numbers in chunks
        DB::table('cashier_hbl_payments')
            ->select('hbl_id', 'invoice_number')
            ->whereNotNull('invoice_number')
            ->orderBy('hbl_id')
            ->chunk(1000, function ($payments) {
                foreach ($payments as $payment) {
                    $this->invoiceNumbers[$payment->hbl_id] = $payment->invoice_number;
                }
            });
    }

    public function query()
    {
        $query = DB::table('hbl')
            ->select([
                'hbl.id',
                'hbl.hbl_number',
                'hbl.created_at',
                'hbl_departure_charges.freight_charge',
                'branches.name as branch_name',
                'users.name as consignee_name',
            ])
            ->join('hbl_departure_charges', 'hbl.id', '=', 'hbl_departure_charges.hbl_id')
            ->leftJoin('branches', 'hbl.branch_id', '=', 'branches.id')
            ->leftJoin('users', 'hbl.consignee_id', '=', 'users.id')
            ->whereNull('hbl.deleted_at')
            ->orderBy('hbl.created_at', 'asc');

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
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('branches.name', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function map($row): array
    {
        $freightCharge = (float) ($row->freight_charge ?? 0);
        $this->grandTotal += $freightCharge;
        $this->rowCount++;

        return [
            date('d/m/Y', strtotime($row->created_at)),
            $row->hbl_number,
            $row->branch_name ?? 'N/A',
            $this->invoiceNumbers[$row->id] ?? '',
            $row->consignee_name ?? 'N/A',
            number_format($freightCharge, 2),
        ];
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Freight Charges ' . $this->dateRange],
            [now()->format('d/m/Y h:i:s A')],
            [],
            [
                'Date',
                'HBL No',
                'Agent Name',
                'Invoice No',
                'Consignee Name',
                'Amount',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Row 1: Company name
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Row 2: Report title
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Row 3: Printed date/time
            3 => [
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            // Row 5: Column headers
            5 => [
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
        return 'Freight Charges';
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

                // Set page setup for A4 size with page numbers
                $sheet->getPageSetup()
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                // Set header and footer for page numbers
                $sheet->getHeaderFooter()
                    ->setOddFooter('&RPAGE - &P');

                // Merge title cells (header rows without borders)
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');

                // Remove borders from header rows (1-3)
                for ($row = 1; $row <= 3; $row++) {
                    for ($col = 'A'; $col <= 'F'; $col++) {
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
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(30);
                $sheet->getColumnDimension('F')->setWidth(15);

                // Apply borders to data rows (starting from row 5)
                $lastRow = 5 + $this->rowCount;
                if ($this->rowCount > 0) {
                    $sheet->getStyle('A5:F' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    // Add grand total row
                    $totalRow = $lastRow + 1;
                    $sheet->setCellValue('A' . $totalRow, 'GRAND TOTAL');
                    $sheet->mergeCells('A' . $totalRow . ':E' . $totalRow);
                    $sheet->setCellValue('F' . $totalRow, number_format($this->grandTotal, 2));

                    // Style grand total row
                    $sheet->getStyle('A' . $totalRow . ':F' . $totalRow)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F3F4F6'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    // Right align amount column
                    $sheet->getStyle('F6:F' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }
}
