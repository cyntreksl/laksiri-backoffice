<?php

namespace App\Exports;

use App\Models\Container;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
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

class ManifestClearanceStatusExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    WithCustomStartCell,
    WithEvents
{
    protected Request $request;
    protected $dateRange;
    protected $reportType;

    public function __construct(Request $request)
    {
        $this->request = $request;

        // Format date range
        $dateFrom = $request->filled('date_from') ? date('d/m/Y', strtotime($request->input('date_from'))) : '';
        $dateTo = $request->filled('date_to') ? date('d/m/Y', strtotime($request->input('date_to'))) : '';

        if ($dateFrom && $dateTo) {
            $this->dateRange = "FROM {$dateFrom} TO {$dateTo}";
        } else {
            $this->dateRange = "FROM 01/03/2026 TO " . date('d/m/Y');
        }

        // Set report type based on pending manifests filter
        $this->reportType = $request->boolean('pending_manifests') ? 'Pending Manifest Listing' : 'Manifest Clearance Status';
    }

    public function query()
    {
        $query = Container::withoutGlobalScope(BranchScope::class)
            ->select([
                'containers.id',
                'containers.manifest_number',
                'containers.container_number',
                'containers.branch_id',
                'containers.estimated_time_of_arrival',
                'containers.created_at'
            ])
            ->whereNotNull('manifest_number')
            ->with(['branch:id,name'])
            // Only include containers that have HBL packages linked to them
            ->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('container_hbl_package')
                    ->join('hbl_packages', 'container_hbl_package.hbl_package_id', '=', 'hbl_packages.id')
                    ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
                    ->whereColumn('container_hbl_package.container_id', 'containers.id')
                    ->whereNull('hbl_packages.deleted_at')
                    ->whereNull('hbl.deleted_at');
            });

        // Apply same filters as controller
        $this->applyFilters($query);

        // Apply sorting
        $query->orderBy('manifest_number', 'asc');

        // Log query for debugging
        \Log::info('Manifest Clearance Status Export Query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'request_params' => $this->request->all(),
            'count' => $query->count()
        ]);

        return $query;
    }

    private function applyFilters($query): void
    {
        // Date range filter
        if ($this->request->filled('date_from')) {
            $query->where('estimated_time_of_arrival', '>=', $this->request->input('date_from'));
        }

        if ($this->request->filled('date_to')) {
            $query->where('estimated_time_of_arrival', '<=', $this->request->input('date_to') . ' 23:59:59');
        }

        // Branch filter (Agent Code)
        if ($this->request->filled('branch_id_from')) {
            $query->where('branch_id', '>=', $this->request->input('branch_id_from'));
        }

        if ($this->request->filled('branch_id_to')) {
            $query->where('branch_id', '<=', $this->request->input('branch_id_to'));
        }

        // Manifest number range filter
        if ($this->request->filled('manifest_number_from')) {
            $query->where('manifest_number', '>=', $this->request->input('manifest_number_from'));
        }

        if ($this->request->filled('manifest_number_to')) {
            $query->where('manifest_number', '<=', $this->request->input('manifest_number_to'));
        }

        // Pending manifests filter - this is the key filter
        if ($this->request->boolean('pending_manifests')) {
            // Only show containers that have NO unclear consignees (Unclear <= 0)
            // This means all consignees have gate passes issued
            $query->whereRaw('
                NOT EXISTS (
                    SELECT 1
                    FROM container_hbl_package chp
                    JOIN hbl_packages hp ON chp.hbl_package_id = hp.id
                    JOIN hbl h ON hp.hbl_id = h.id
                    LEFT JOIN examinations e ON h.id = e.hbl_id
                    WHERE chp.container_id = containers.id
                    AND hp.deleted_at IS NULL
                    AND h.deleted_at IS NULL
                    AND (e.is_issued_gate_pass = 0 OR e.is_issued_gate_pass IS NULL)
                )
            ');
        }

        // General search
        if ($this->request->filled('search')) {
            $search = $this->request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('manifest_number', 'like', "%{$search}%")
                    ->orWhere('container_number', 'like', "%{$search}%");
            });
        }
    }

    public function map($container): array
    {
        // Get consignee counts for this container
        $consigneeStats = DB::table('container_hbl_package')
            ->join('hbl_packages', 'container_hbl_package.hbl_package_id', '=', 'hbl_packages.id')
            ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
            ->leftJoin('examinations', 'hbl.id', '=', 'examinations.hbl_id')
            ->where('container_hbl_package.container_id', $container->id)
            ->whereNull('hbl_packages.deleted_at')
            ->whereNull('hbl.deleted_at')
            ->selectRaw('
                COUNT(DISTINCT hbl.id) as total_consignees,
                COALESCE(COUNT(DISTINCT CASE WHEN examinations.is_issued_gate_pass = 1 THEN hbl.id END), 0) as cleared_consignees,
                COALESCE(COUNT(DISTINCT CASE WHEN examinations.is_issued_gate_pass = 0 OR examinations.is_issued_gate_pass IS NULL THEN hbl.id END), 0) as unclear_consignees
            ')
            ->first();

        // Ensure all values are integers, explicitly convert to int and ensure minimum 0
        $totalConsignees = (int) ($consigneeStats->total_consignees ?? 0);
        $clearedConsignees = (int) ($consigneeStats->cleared_consignees ?? 0);
        $unclearConsignees = (int) ($consigneeStats->unclear_consignees ?? 0);

        // If we have consignees but no examinations, all should be unclear
        if ($totalConsignees > 0 && ($clearedConsignees + $unclearConsignees) == 0) {
            $unclearConsignees = $totalConsignees;
        }

        // Double check: if we have consignees but cleared is null/empty, set to 0
        if ($totalConsignees > 0 && $clearedConsignees === null) {
            $clearedConsignees = 0;
        }

        \Log::info('Mapping container for export', [
            'container_id' => $container->id,
            'manifest_number' => $container->manifest_number,
            'total_consignees' => $totalConsignees,
            'cleared_consignees' => $clearedConsignees,
            'unclear_consignees' => $unclearConsignees,
            'raw_stats' => $consigneeStats
        ]);

        return [
            $container->manifest_number ?? '',
            $container->branch?->name ?? '',
            $container->container_number ?? '',
            '', // Container No 2 - empty
            '', // Container No 3 - empty
            $container->estimated_time_of_arrival ? $container->estimated_time_of_arrival->format('d/m/Y') : '',
            (int) $totalConsignees,      // Explicitly cast to int
            (int) $clearedConsignees,    // Explicitly cast to int
            (int) $unclearConsignees,    // Explicitly cast to int
        ];
    }

    public function headings(): array
    {
        $printedDateTime = date('d/m/Y H:i:s');

        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            [$this->reportType],
            [$this->dateRange],
            [$printedDateTime],
            [],
            [
                'Ref. No',
                'Agent Name',
                'Container No 1',
                'Container No 2',
                'Container No 3',
                'Arrival Date',
                'Consignees Recei.',
                'Clear',
                'Unclear'
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
                'font' => ['bold' => false, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            6 => [
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
        return 'Manifest Clearance Status';
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
                $sheet->mergeCells('A1:I1');
                $sheet->mergeCells('A2:I2');
                $sheet->mergeCells('A3:I3');
                $sheet->mergeCells('A4:I4');

                // Remove borders from header rows (1-5)
                for ($row = 1; $row <= 5; $row++) {
                    for ($col = 'A'; $col <= 'I'; $col++) {
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
                $sheet->getColumnDimension('A')->setWidth(15); // Ref. No
                $sheet->getColumnDimension('B')->setWidth(25); // Agent Name
                $sheet->getColumnDimension('C')->setWidth(15); // Container No 1
                $sheet->getColumnDimension('D')->setWidth(15); // Container No 2
                $sheet->getColumnDimension('E')->setWidth(15); // Container No 3
                $sheet->getColumnDimension('F')->setWidth(12); // Arrival Date
                $sheet->getColumnDimension('G')->setWidth(12); // Consignees Recei.
                $sheet->getColumnDimension('H')->setWidth(8);  // Clear
                $sheet->getColumnDimension('I')->setWidth(8);  // Unclear

                // Set header row height
                $sheet->getRowDimension(6)->setRowHeight(30);

                // Get the last row with data
                $lastRow = $sheet->getHighestRow();

                if ($lastRow > 6) {
                    // Apply borders to data rows
                    $sheet->getStyle('A6:I' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'font' => ['size' => 8],
                    ]);

                    // Center align specific columns and format as numbers
                    $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Ref. No
                    $sheet->getStyle('C7:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Container numbers
                    $sheet->getStyle('F7:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Arrival Date
                    $sheet->getStyle('G7:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Consignee counts

                    // Format consignee columns as numbers
                    $sheet->getStyle('G7:I' . $lastRow)->getNumberFormat()->setFormatCode('0');

                    // Calculate Grand Total values manually and ensure all cells have numeric values
                    $totalReceived = 0;
                    $totalCleared = 0;
                    $totalUnclear = 0;

                    // Sum up the values from data rows (starting from row 7) and ensure 0 values are set
                    for ($row = 7; $row <= $lastRow; $row++) {
                        // Get values and ensure they're integers
                        $receivedVal = (int) ($sheet->getCell('G' . $row)->getValue() ?? 0);
                        $clearedVal = (int) ($sheet->getCell('H' . $row)->getValue() ?? 0);
                        $unclearVal = (int) ($sheet->getCell('I' . $row)->getValue() ?? 0);

                        // Explicitly set the cell values to ensure 0s are displayed
                        $sheet->setCellValue('G' . $row, $receivedVal);
                        $sheet->setCellValue('H' . $row, $clearedVal);
                        $sheet->setCellValue('I' . $row, $unclearVal);

                        $totalReceived += $receivedVal;
                        $totalCleared += $clearedVal;
                        $totalUnclear += $unclearVal;
                    }

                    // Add Grand Total row
                    $grandTotalRow = $lastRow + 1;
                    $sheet->setCellValue('A' . $grandTotalRow, 'Grand Total');
                    $sheet->mergeCells('A' . $grandTotalRow . ':F' . $grandTotalRow);

                    // Set calculated totals as values (not formulas)
                    $sheet->setCellValue('G' . $grandTotalRow, $totalReceived);
                    $sheet->setCellValue('H' . $grandTotalRow, $totalCleared);
                    $sheet->setCellValue('I' . $grandTotalRow, $totalUnclear);

                    // Style Grand Total row
                    $sheet->getStyle('A' . $grandTotalRow . ':I' . $grandTotalRow)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                        ],
                    ]);
                }
            },
        ];
    }
}
