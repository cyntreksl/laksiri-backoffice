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

class ConsigneeClearanceDetailsExport implements
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
        $query = DB::table('examinations')
            ->join('hbl', 'examinations.hbl_id', '=', 'hbl.id')
            ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
            ->leftJoin('cashier_hbl_payments', 'hbl.id', '=', 'cashier_hbl_payments.hbl_id')
            ->select([
                'hbl.hbl_number',
                'consignees.name as consignee_name',
                'cashier_hbl_payments.invoice_number',
                'cashier_hbl_payments.created_at as inv_date',
                'examinations.created_at as det_date',
            ])
            ->whereNotNull('examinations.released_at') // Use released_at as clearance indicator
            ->whereNull('hbl.deleted_at')
            ->where('is_issued_gate_pass', TRUE)
            ->orderBy('examinations.created_at', 'desc');

        // Apply date range filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('examinations.created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('examinations.created_at', '<=', $this->filters['date_to']);
        }

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('hbl.hbl_number', 'like', "%{$search}%")
                    ->orWhere('consignees.name', 'like', "%{$search}%")
                    ->orWhere('cashier_hbl_payments.invoice_number', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function map($row): array
    {
        $this->rowCount++;

        return [
            $this->rowCount, // Serial No
            $row->hbl_number,
            $row->consignee_name,
            $row->invoice_number,
            $row->inv_date ? date('d/m/Y', strtotime($row->inv_date)) : '',
//            $row->det_date ? date('d/m/Y', strtotime($row->det_date)) : '',
            '',
        ];
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Consignee Clearance Details'],
            [$this->dateRange],
            [date('d/m/Y') . '  ' . date('H:i:s'), '', '', '', '', 'PAGE - 1'],
            [],
            [
                'Serial No.',
                'HBL No.',
                'Consignee Name',
                'Invoice No.',
                'Inv. Date',
                'Det',
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
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Consignee Clearance Details';
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
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');

                // Remove borders from header rows (1-4)
                for ($row = 1; $row <= 4; $row++) {
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
                $sheet->getColumnDimension('A')->setWidth(12); // Serial No.
                $sheet->getColumnDimension('B')->setWidth(15); // HBL No.
                $sheet->getColumnDimension('C')->setWidth(35); // Consignee Name
                $sheet->getColumnDimension('D')->setWidth(15); // Invoice No.
                $sheet->getColumnDimension('E')->setWidth(12); // Inv. Date
                $sheet->getColumnDimension('F')->setWidth(12); // Det

                // Set header row height to accommodate text
                $sheet->getRowDimension(6)->setRowHeight(30);

                // Apply borders to data rows (starting from row 6)
                if ($this->rowCount > 0) {
                    $lastRow = 6 + $this->rowCount;

                    $sheet->getStyle('A6:F' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    // Center align serial number
                    $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Center align dates
                    $sheet->getStyle('E7:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
