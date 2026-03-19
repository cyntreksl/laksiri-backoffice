<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

class ConsigneeVolumeAnalysisExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    WithCustomStartCell,
    WithEvents
{
    protected Request $request;
    protected $dateRange;
    protected $reportTitle;
    protected $analysisType;
    protected $isOutgoing;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->analysisType = $request->input('analysis_type', 'remaining');
        $this->isOutgoing = $this->analysisType === 'outgoing';

        // Format date range
        $dateFrom = $request->filled('date_from') ? date('d/m/Y', strtotime($request->input('date_from'))) : '';
        $dateTo = $request->filled('date_to') ? date('d/m/Y', strtotime($request->input('date_to'))) : '';

        if ($dateFrom && $dateTo) {
            $this->dateRange = "From {$dateFrom} To {$dateTo}";
        } else {
            $this->dateRange = "From 01/03/2026 To " . date('d/m/Y');
        }

        // Set report title based on analysis type
        if ($this->isOutgoing) {
            $this->reportTitle = 'Agent Wise Total Outgoing Consignee & Volume Of Cargo Analysis';
        } else {
            $this->reportTitle = 'Agent Wise Total Remaining Consignee & Volume Of Cargo Analysis';
        }
    }

    public function collection()
    {
        $gatePassValue = $this->isOutgoing ? 1 : 0;

        $query = DB::table('examinations as e')
            ->join('hbl as h', 'e.hbl_id', '=', 'h.id')
            ->join('branches as b', 'h.branch_id', '=', 'b.id')
            ->join('hbl_packages as hp', 'h.id', '=', 'hp.hbl_id')
            ->where('e.is_issued_gate_pass', $gatePassValue)
            ->whereNull('h.deleted_at')
            ->whereNull('hp.deleted_at')
            ->select([
                'h.branch_id',
                'b.name as agent_name',
                DB::raw('COUNT(DISTINCT h.id) as no_of_consignees'),
                DB::raw('SUM(hp.quantity) as no_of_pkgs_manifest'),
                DB::raw('SUM(hp.quantity) as no_of_pkgs_actual'),
                DB::raw('SUM(hp.volume) as cbm')
            ])
            ->groupBy('h.branch_id', 'b.name');

        // Apply same filters as controller
        $this->applyFilters($query);

        $results = $query->orderBy('b.name')->get();

        // Add Grand Total row
        $grandTotal = (object) [
            'branch_id' => null,
            'agent_name' => 'Grand Total',
            'no_of_consignees' => $results->sum('no_of_consignees'),
            'no_of_pkgs_manifest' => $results->sum('no_of_pkgs_manifest'),
            'no_of_pkgs_actual' => $results->sum('no_of_pkgs_actual'),
            'cbm' => number_format($results->sum('cbm'), 2),
        ];

        $results->push($grandTotal);

        // Log query for debugging
        \Log::info('Consignee Volume Analysis Export Query', [
            'analysis_type' => $this->analysisType,
            'is_outgoing' => $this->isOutgoing,
            'request_params' => $this->request->all(),
            'count' => $results->count()
        ]);

        return $results;
    }

    private function applyFilters($query): void
    {
        // Date range filter
        if ($this->request->filled('date_from')) {
            $query->whereDate('e.created_at', '>=', $this->request->input('date_from'));
        }

        if ($this->request->filled('date_to')) {
            $query->whereDate('e.created_at', '<=', $this->request->input('date_to'));
        }

        // Branch filter
        if ($this->request->filled('branch_id')) {
            $query->where('h.branch_id', $this->request->input('branch_id'));
        }

        // General search
        if ($this->request->filled('search')) {
            $search = $this->request->input('search');
            $query->where('b.name', 'like', "%{$search}%");
        }
    }

    public function map($row): array
    {
        return [
            $row->agent_name ?? '',
            (int) ($row->no_of_consignees ?? 0),
            (int) ($row->no_of_pkgs_manifest ?? 0),
            (int) ($row->no_of_pkgs_actual ?? 0),
            number_format((float) ($row->cbm ?? 0), 2),
        ];
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            [$this->reportTitle],
            [$this->dateRange, '', '', '', 'PAGE - 1'],
            [],
            [
                'Agent Name',
                'No of Consignees',
                'No of PKgs (Manifest)',
                'No of PKgs (Actual)',
                'CBM'
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
                'font' => ['bold' => false, 'size' => 10],
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
        return 'Consignee Volume Analysis';
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
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');

                // Remove borders from header rows (1-4)
                for ($row = 1; $row <= 4; $row++) {
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
                $sheet->getColumnDimension('B')->setWidth(15); // No of Consignees
                $sheet->getColumnDimension('C')->setWidth(18); // No of PKgs (Manifest)
                $sheet->getColumnDimension('D')->setWidth(18); // No of PKgs (Actual)
                $sheet->getColumnDimension('E')->setWidth(12); // CBM

                // Set header row height
                $sheet->getRowDimension(5)->setRowHeight(30);

                // Get the last row with data
                $lastRow = $sheet->getHighestRow();

                if ($lastRow > 5) {
                    // Apply borders to data rows
                    $sheet->getStyle('A5:E' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'font' => ['size' => 9],
                    ]);

                    // Center align numeric columns
                    $sheet->getStyle('B6:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Format numeric columns
                    $sheet->getStyle('B6:D' . $lastRow)->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle('E6:E' . $lastRow)->getNumberFormat()->setFormatCode('0.00');

                    // Style Grand Total row (last row)
                    $sheet->getStyle('A' . $lastRow . ':E' . $lastRow)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F3F4F6'],
                        ],
                    ]);

                    // Set page number alignment
                    $sheet->getStyle('E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }
}