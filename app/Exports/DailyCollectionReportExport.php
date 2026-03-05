<?php

namespace App\Exports;

use App\Models\CashierHblPayment;
use Maatwebsite\Excel\Concerns\FromCollection;
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

class DailyCollectionReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    WithCustomStartCell,
    WithEvents
{
    protected $filters;
    protected $stats;
    protected $rowCount = 0;
    protected $reportDate;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->reportDate = $filters['date'] ?? now()->format('d/m/Y');
    }

    public function collection()
    {
        $query = CashierHblPayment::query()
            ->whereNotNull('invoice_number')
            ->orderBy('created_at', 'asc');

        // Apply date filter
        if (!empty($this->filters['date'])) {
            $query->whereDate('created_at', $this->filters['date']);
        }

        // Apply date range filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%");
            });
        }

        $payments = $query->get();

        // Transform data with serial numbers and tax calculations
        $records = $payments->map(function ($payment, $index) {
            // VAT and NBT are stored directly in the payment record
            $vatAmount = (float) ($payment->destination_1_tax ?? 0);
            $nbtAmount = (float) (0);

            // Calculate total amount from all charges
            $departureTotal = (float) ($payment->departure_grand_total ?? 0);
            $destinationTotal = (float) ($payment->destination_1_total_with_tax ?? 0) +
                               (float) ($payment->destination_2_total_with_tax ?? 0);
            $totalAmount = $departureTotal + $destinationTotal;

            // If paid_amount is set and greater than 0, use it as the total
            if ($payment->paid_amount > 0) {
                $totalAmount = (float) $payment->paid_amount;
            }

            return [
                'serial_no' => $index + 1,
                'invoice_number' => $payment->invoice_number,
                'vat' => $vatAmount,
                'nbt' => $nbtAmount,
                'total_amount' => $totalAmount,
            ];
        });

        // Calculate statistics
        $this->stats = [
            'total_vat' => $records->sum('vat'),
            'total_nbt' => $records->sum('nbt'),
            'grand_total' => $records->sum('total_amount'),
        ];

        $this->rowCount = $records->count();

        return $records;
    }

    public function map($row): array
    {
        return [
            $row['serial_no'],
            $row['invoice_number'],
            number_format($row['vat'], 2),
            number_format($row['nbt'], 2),
            number_format($row['total_amount'], 2),
        ];
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Daily Collection Record for ' . $this->reportDate],
            [],
            [
                'Serial No',
                'Invoice No',
                'Vat',
                'NBT',
                'Total Amount',
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
            // Row 4: Column headers
            4 => [
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
        return 'Daily Collection';
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

                // Merge title cells (header rows without borders)
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');

                // Remove borders from header rows (1-2)
                for ($row = 1; $row <= 2; $row++) {
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
                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(18);

                // Apply borders to data rows (starting from row 4)
                $lastRow = 4 + $this->rowCount;
                $sheet->getStyle('A4:E' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Add grand total row
                $totalRow = $lastRow + 1;
                $sheet->setCellValue('A' . $totalRow, 'Grand Total:');
                $sheet->mergeCells('A' . $totalRow . ':B' . $totalRow);
                $sheet->setCellValue('C' . $totalRow, number_format($this->stats['total_vat'], 2));
                $sheet->setCellValue('D' . $totalRow, number_format($this->stats['total_nbt'], 2));
                $sheet->setCellValue('E' . $totalRow, number_format($this->stats['grand_total'], 2));

                // Style grand total row
                $sheet->getStyle('A' . $totalRow . ':E' . $totalRow)->applyFromArray([
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

                // Center align numeric columns
                $sheet->getStyle('A5:A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C5:E' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
