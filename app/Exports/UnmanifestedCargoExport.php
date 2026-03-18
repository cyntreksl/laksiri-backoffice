<?php

namespace App\Exports;

use App\Models\HBL;
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

class UnmanifestedCargoExport implements
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
    }

    public function query()
    {
        $query = HBL::withoutGlobalScope(BranchScope::class)
            ->select([
                'hbl.id',
                'hbl.hbl_number',
                'hbl.consignee_name',
                'hbl.consignee_address',
                'hbl.branch_id',
                'hbl.created_at'
            ])
            ->with([
                'branch:id,name',
                'packages' => function ($query) {
                    $query->select('id', 'hbl_id', 'package_type', 'quantity', 'unloaded_at')
                          ->where('is_unloaded', true);
                }
            ])
            ->whereHas('packages', function ($query) {
                $query->where('is_unloaded', true);
            });

        // Apply same filters as controller
        $this->applyFilters($query);

        // Apply sorting
        $query->orderBy('hbl_number', 'asc');

        // Log query for debugging
        \Log::info('Unmanifested Cargo Export Query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'request_params' => $this->request->all(),
            'count' => $query->count()
        ]);

        return $query;
    }

    private function applyFilters($query): void
    {
        // Date range filter (unloading date)
        if ($this->request->filled('date_from')) {
            $query->whereHas('packages', function ($q) {
                $q->where('is_unloaded', true)
                  ->whereDate('unloaded_at', '>=', $this->request->input('date_from'));
            });
        }

        if ($this->request->filled('date_to')) {
            $query->whereHas('packages', function ($q) {
                $q->where('is_unloaded', true)
                  ->whereDate('unloaded_at', '<=', $this->request->input('date_to'));
            });
        }

        // Manifest number range filter
        if ($this->request->filled('manifest_number_from')) {
            $query->whereHas('packages.containers', function ($q) {
                $q->where('manifest_number', '>=', $this->request->input('manifest_number_from'));
            });
        }

        if ($this->request->filled('manifest_number_to')) {
            $query->whereHas('packages.containers', function ($q) {
                $q->where('manifest_number', '<=', $this->request->input('manifest_number_to'));
            });
        }

        // Branch filter
        if ($this->request->filled('branch_id')) {
            $query->where('branch_id', $this->request->input('branch_id'));
        }

        // General search
        if ($this->request->filled('search')) {
            $search = $this->request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('hbl_number', 'like', "%{$search}%")
                    ->orWhere('consignee_name', 'like', "%{$search}%");
            });
        }
    }

    public function map($hbl): array
    {
        // Get the manifest number from related containers
        $manifestNumber = '';
        $destuffDate = '';
        
        if ($hbl->packages->isNotEmpty()) {
            $package = $hbl->packages->first();
            $container = $package->containers()->first();
            if ($container) {
                $manifestNumber = $container->manifest_number ?? '';
            }
            $destuffDate = $package->unloaded_at ? $package->unloaded_at->format('d/m/Y') : '';
        }

        // Get package details
        $packageTypes = $hbl->packages->pluck('package_type')->unique()->implode(', ');
        $totalQuantity = (int) $hbl->packages->sum('quantity');

        return [
            $hbl->branch?->name ?? '',
            $destuffDate,
            $hbl->hbl_number ?? '',
            trim(($hbl->consignee_name ?? '') . ' ' . ($hbl->consignee_address ?? '')),
            $packageTypes,
            $totalQuantity,
        ];
    }

    public function headings(): array
    {
        $printedDateTime = date('d/m/Y H:i:s');

        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Unmanifested Cargo Report'],
            [$this->dateRange],
            [$printedDateTime],
            [],
            [
                'Agent Name',
                'Destuff Date',
                'HBL No',
                'Consignee\'s Name & Address',
                'Type of Package',
                'Qty'
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
        return 'Unmanifested Cargo';
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
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('A4:F4');

                // Remove borders from header rows (1-5)
                for ($row = 1; $row <= 5; $row++) {
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
                $sheet->getColumnDimension('A')->setWidth(25); // Agent Name
                $sheet->getColumnDimension('B')->setWidth(15); // Destuff Date
                $sheet->getColumnDimension('C')->setWidth(20); // HBL No
                $sheet->getColumnDimension('D')->setWidth(40); // Consignee's Name & Address
                $sheet->getColumnDimension('E')->setWidth(20); // Type of Package
                $sheet->getColumnDimension('F')->setWidth(10); // Qty

                // Set header row height
                $sheet->getRowDimension(6)->setRowHeight(30);

                // Get the last row with data
                $lastRow = $sheet->getHighestRow();

                if ($lastRow > 6) {
                    // Apply borders to data rows
                    $sheet->getStyle('A6:F' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'font' => ['size' => 8],
                    ]);

                    // Center align specific columns
                    $sheet->getStyle('B7:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Destuff Date
                    $sheet->getStyle('C7:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // HBL No
                    $sheet->getStyle('F7:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Qty

                    // Format quantity column as numbers
                    $sheet->getStyle('F7:F' . $lastRow)->getNumberFormat()->setFormatCode('0');

                    // Calculate Grand Total values manually
                    $totalQuantity = 0;

                    // Sum up the values from data rows (starting from row 7)
                    for ($row = 7; $row <= $lastRow; $row++) {
                        $qtyVal = (int) ($sheet->getCell('F' . $row)->getValue() ?? 0);
                        $sheet->setCellValue('F' . $row, $qtyVal);
                        $totalQuantity += $qtyVal;
                    }

                    // Add Grand Total row
                    $grandTotalRow = $lastRow + 1;
                    $sheet->setCellValue('A' . $grandTotalRow, 'Grand Total');
                    $sheet->mergeCells('A' . $grandTotalRow . ':E' . $grandTotalRow);

                    // Set calculated total as value (not formula)
                    $sheet->setCellValue('F' . $grandTotalRow, $totalQuantity);

                    // Style Grand Total row
                    $sheet->getStyle('A' . $grandTotalRow . ':F' . $grandTotalRow)->applyFromArray([
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