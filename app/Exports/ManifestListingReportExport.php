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

class ManifestListingReportExport implements
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
    protected $agentName;

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

        // Get agent name if filtered
        if ($request->filled('branch_id')) {
            $branch = DB::table('branches')->where('id', $request->input('branch_id'))->first();
            $this->agentName = $branch ? $branch->name : 'ALL AGENTS';
        } else {
            $this->agentName = 'ALL AGENTS';
        }
    }

    public function query()
    {
        $query = Container::withoutGlobalScope(BranchScope::class)
            ->select([
                'containers.id',
                'containers.reference',
                'containers.manifest_number',
                'containers.manifest_generated_at',
                'containers.manifest_generated_by',
                'containers.branch_id',
                'containers.vessel_name',
                'containers.created_at',
                // Add package counts as subqueries for better performance
                DB::raw('(SELECT COUNT(*) FROM container_hbl_package WHERE container_id = containers.id) as package_count'),
                DB::raw('(SELECT COUNT(DISTINCT hbl_packages.hbl_id) FROM container_hbl_package
                         JOIN hbl_packages ON container_hbl_package.hbl_package_id = hbl_packages.id
                         WHERE container_hbl_package.container_id = containers.id
                         AND hbl_packages.deleted_at IS NULL) as consignee_count')
            ])
            ->whereNotNull('manifest_number')
            ->with([
                'branch:id,name',
                'manifestGeneratedByUser:id,name'
            ]);

        // Apply same filters as controller
        $this->applyFilters($query);

        // Apply sorting
        $sortField = $this->request->input('sort_field', 'manifest_generated_at');
        $sortOrder = $this->request->input('sort_order', 'desc');

        $sortableFields = [
            'manifest_number' => 'manifest_number',
            'manifest_generated_at' => 'manifest_generated_at',
            'vessel_name' => 'vessel_name',
            'created_at' => 'created_at',
        ];

        $dbSortField = $sortableFields[$sortField] ?? 'manifest_generated_at';
        $query->orderBy($dbSortField, $sortOrder);

        // Log query for debugging
        \Log::info('Manifest Export Query', [
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
            $query->where('manifest_generated_at', '>=', $this->request->input('date_from'));
        }

        if ($this->request->filled('date_to')) {
            $query->where('manifest_generated_at', '<=', $this->request->input('date_to') . ' 23:59:59');
        }

        // Branch filter (Agent Name)
        if ($this->request->filled('branch_id')) {
            $query->where('branch_id', $this->request->input('branch_id'));
        }

        // Created User filter
        if ($this->request->filled('created_user_id')) {
            $query->where('manifest_generated_by', $this->request->input('created_user_id'));
        }

        // Manifest number range filter
        if ($this->request->filled('manifest_number_from')) {
            $query->where('manifest_number', '>=', $this->request->input('manifest_number_from'));
        }

        if ($this->request->filled('manifest_number_to')) {
            $query->where('manifest_number', '<=', $this->request->input('manifest_number_to'));
        }

        // General search
        if ($this->request->filled('search')) {
            $search = $this->request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('manifest_number', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('vessel_name', 'like', "%{$search}%");
            });
        }
    }

    public function map($container): array
    {
        // Use precomputed counts from the query
        $packageCount = $container->package_count ?? 0;
        $consigneeCount = $container->consignee_count ?? 0;

        \Log::info('Mapping container for export', [
            'container_id' => $container->id,
            'manifest_number' => $container->manifest_number,
            'package_count' => $packageCount,
            'consignee_count' => $consigneeCount,
            'branch_name' => $container->branch?->name,
            'user_name' => $container->manifestGeneratedByUser?->name
        ]);

        return [
            $container->manifest_number ?? '',
            $container->manifest_generated_at ? $container->manifest_generated_at->format('d/m/Y') : '',
            $container->branch?->name ?? '',
            $container->vessel_name ?? '',
            $consigneeCount,
            $packageCount,
            $container->manifestGeneratedByUser?->name ?? ''
        ];
    }

    public function headings(): array
    {
        $printedDateTime = date('d/m/Y H:i:s');

        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Manifest Listing ' . $this->dateRange],
            ['AGENT NAME : ' . $this->agentName],
            [$printedDateTime, '', '', '', '', '', 'Page No : 1'],
            [],
            [
                'MANIFEST NO',
                'DATE',
                'AGENT NAME',
                'VESSEL NAME',
                'NO OF CONSIGNEE',
                'NO OF PACKAGES',
                'User Id'
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
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
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
        return 'Manifest Listing';
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
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');
                $sheet->mergeCells('A3:G3');
                $sheet->mergeCells('A4:F4'); // Leave G4 for page number

                // Remove borders from header rows (1-5)
                for ($row = 1; $row <= 5; $row++) {
                    for ($col = 'A'; $col <= 'G'; $col++) {
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
                $sheet->getColumnDimension('A')->setWidth(15); // Manifest No
                $sheet->getColumnDimension('B')->setWidth(12); // Date
                $sheet->getColumnDimension('C')->setWidth(20); // Agent Name
                $sheet->getColumnDimension('D')->setWidth(35); // Vessel Name
                $sheet->getColumnDimension('E')->setWidth(15); // No of Consignee
                $sheet->getColumnDimension('F')->setWidth(15); // No of Packages
                $sheet->getColumnDimension('G')->setWidth(15); // User Id

                // Set header row height
                $sheet->getRowDimension(6)->setRowHeight(30);

                // Get the last row with data
                $lastRow = $sheet->getHighestRow();

                if ($lastRow > 6) {
                    // Apply borders to data rows
                    $sheet->getStyle('A6:G' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'font' => ['size' => 8],
                    ]);

                    // Center align specific columns
                    $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Manifest No
                    $sheet->getStyle('B7:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Date
                    $sheet->getStyle('E7:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No of Consignee
                    $sheet->getStyle('F7:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No of Packages
                }

                // Style page number in G4
                $sheet->getStyle('G4')->applyFromArray([
                    'font' => ['bold' => false, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
            },
        ];
    }
}
