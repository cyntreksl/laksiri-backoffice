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

class LetterRegistrationRecordsExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    WithCustomStartCell,
    WithEvents
{
    protected Request $request;
    protected $manifestNumber;
    protected $agentName;
    protected $containerNumber;
    protected $destuffingDate;
    protected $serialCounter = 1;

    public function __construct(Request $request)
    {
        $this->request = $request;

        // Get container and manifest details for header
        $this->getContainerDetails();

        $this->destuffingDate = date('d/m/Y') . '    ' . date('H:i:s');
    }

    private function getContainerDetails(): void
    {
        $this->manifestNumber = '';
        $this->agentName = '';
        $this->containerNumber = '';

        // Get details from the first matching record
        $query = $this->buildQuery();
        $firstHbl = $query->with(['packages.containers', 'branch'])->first();

        if ($firstHbl && $firstHbl->packages->isNotEmpty()) {
            $package = $firstHbl->packages->first();
            if ($package->containers->isNotEmpty()) {
                $container = $package->containers->first();
                $this->manifestNumber = $container->manifest_number ?? '';
                $this->containerNumber = $container->container_number ?? '';
            }
            $this->agentName = $firstHbl->branch?->name ?? '';
        }
    }

    private function buildQuery()
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
                    $query->select('id', 'hbl_id', 'package_type', 'quantity', 'weight')
                          ->with(['containers:id,manifest_number,container_number']);
                }
            ])
            ->whereHas('packages.containers');

        // Apply same filters as controller
        $this->applyFilters($query);

        return $query;
    }

    private function applyFilters($query): void
    {
        // Manifest number filter
        if ($this->request->filled('manifest_number')) {
            $query->whereHas('packages.containers', function ($q) {
                $q->where('manifest_number', 'like', '%' . $this->request->input('manifest_number') . '%');
            });
        }

        // Branch filter
        if ($this->request->filled('branch_id')) {
            $query->where('branch_id', $this->request->input('branch_id'));
        }

        // Container filter
        if ($this->request->filled('container_id')) {
            $query->whereHas('packages.containers', function ($q) {
                $q->where('containers.id', $this->request->input('container_id'));
            });
        }

        // General search
        if ($this->request->filled('search')) {
            $search = $this->request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('hbl_number', 'like', "%{$search}%")
                    ->orWhere('consignee_name', 'like', "%{$search}%")
                    ->orWhere('consignee_address', 'like', "%{$search}%");
            });
        }
    }

    public function query()
    {
        $query = $this->buildQuery();

        // Apply sorting
        $query->orderBy('hbl_number', 'asc');

        // Log query for debugging
        \Log::info('Letter Registration Records Export Query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'request_params' => $this->request->all(),
            'count' => $query->count()
        ]);

        return $query;
    }

    public function map($hbl): array
    {
        // Get container and manifest information
        $manifestNumber = '';
        $containerNumber = '';

        if ($hbl->packages->isNotEmpty()) {
            $package = $hbl->packages->first();
            if ($package->containers->isNotEmpty()) {
                $container = $package->containers->first();
                $manifestNumber = $container->manifest_number ?? '';
                $containerNumber = $container->container_number ?? '';
            }
        }

        // Get package details
        $totalPackages = (int) $hbl->packages->sum('quantity');
        $totalWeight = (float) $hbl->packages->sum('weight');

        return [
            $this->serialCounter++,
            $hbl->hbl_number ?? '',
            trim(($hbl->consignee_name ?? '') . ' ' . ($hbl->consignee_address ?? '')),
            '', // Remarks - empty for now
            $totalPackages,
            number_format($totalWeight, 2),
        ];
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Letter Registration Records'],
            [],
            ['CARGO MANIFEST NO.', ':', $this->manifestNumber],
            ['AGENT', ':', $this->agentName],
            ['CONTAINER NO.', ':', $this->containerNumber],
            ['DESTUFFING DATE', ':', ''],
            [],
            [$this->destuffingDate, '', '', '', '', 'PAGE - 1'],
            [
                'Serial No',
                'HBL No',
                'Consignee\'s Name & Address',
                'Remarks',
                'PKgs',
                'CB'
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
            4 => [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            5 => [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            6 => [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            7 => [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            9 => [
                'font' => ['bold' => false, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            10 => [
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
        return 'Letter Registration Records';
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

                // Set page setup for A4 size portrait
                $sheet->getPageSetup()
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);

                // Merge title cells
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');

                // Remove borders from header rows (1-9)
                for ($row = 1; $row <= 9; $row++) {
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
                $sheet->getColumnDimension('A')->setWidth(10); // Serial No
                $sheet->getColumnDimension('B')->setWidth(15); // HBL No
                $sheet->getColumnDimension('C')->setWidth(40); // Consignee's Name & Address
                $sheet->getColumnDimension('D')->setWidth(15); // Remarks
                $sheet->getColumnDimension('E')->setWidth(8);  // PKgs
                $sheet->getColumnDimension('F')->setWidth(10); // CB

                // Set header row height
                $sheet->getRowDimension(10)->setRowHeight(30);

                // Get the last row with data
                $lastRow = $sheet->getHighestRow();

                if ($lastRow > 10) {
                    // Apply borders to data rows
                    $sheet->getStyle('A10:F' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                        'font' => ['size' => 8],
                    ]);

                    // Center align specific columns
                    $sheet->getStyle('A11:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Serial No
                    $sheet->getStyle('B11:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // HBL No
                    $sheet->getStyle('E11:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // PKgs
                    $sheet->getStyle('F11:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // CB

                    // Format numeric columns
                    $sheet->getStyle('E11:E' . $lastRow)->getNumberFormat()->setFormatCode('0');
                    $sheet->getStyle('F11:F' . $lastRow)->getNumberFormat()->setFormatCode('0.00');

                    // Set row heights for data rows
                    for ($row = 11; $row <= $lastRow; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(25);
                    }
                }

                // Set specific cell alignments for header information
                $sheet->getStyle('E9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
