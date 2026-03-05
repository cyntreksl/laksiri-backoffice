<?php

namespace App\Exports;

use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\Scopes\BranchScope;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AgentWiseContainerArrivalSummaryExport implements
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

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Container::withoutGlobalScope(BranchScope::class)
            ->whereIn('status', [
                ContainerStatus::IN_TRANSIT->value,
                ContainerStatus::REACHED_DESTINATION->value,
                ContainerStatus::ARRIVED_PRIMARY_WAREHOUSE->value,
            ])
            ->with([
                'branch:id,name',
                'hbl_packages' => function ($query) {
                    $query->with(['hbl' => function ($q) {
                        $q->select('id', 'hbl_number', 'consignee_name');
                    }]);
                }
            ])
            ->whereNotNull('unloading_started_at');

        // Apply date filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('unloading_started_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('unloading_started_at', '<=', $this->filters['date_to']);
        }

        // Apply branch filter
        if (!empty($this->filters['branch_id'])) {
            $query->where('branch_id', $this->filters['branch_id']);
        }

        $containers = $query->get();

        // Manually load packages for each container to bypass soft delete issues
        foreach ($containers as $container) {
            $packageIds = \DB::table('container_hbl_package')
                ->where('container_id', $container->id)
                ->where('status', 'loaded')
                ->pluck('hbl_package_id');

            if ($packageIds->isNotEmpty()) {
                $container->loaded_packages = \DB::table('hbl_packages')
                    ->whereIn('id', $packageIds)
                    ->get();

                // Get HBL info for each package
                $hblIds = $container->loaded_packages->pluck('hbl_id')->unique();
                $hbls = \DB::table('hbl')
                    ->whereIn('id', $hblIds)
                    ->get()
                    ->keyBy('id');

                // Attach HBL to each package
                foreach ($container->loaded_packages as $package) {
                    $package->hbl = $hbls->get($package->hbl_id);
                }
            } else {
                $container->loaded_packages = collect();
            }
        }

        // Group by agent/branch
        $agentData = [];

        foreach ($containers as $container) {
            $agentName = $container->branch->name ?? 'Unknown Agent';

            if (!isset($agentData[$agentName])) {
                $agentData[$agentName] = [
                    'agent_name' => $agentName,
                    'total_cbm' => 0,
                    'wooden_boxes' => 0,
                    'steel_trunk' => 0,
                    'other_packages' => 0,
                    'total_packages' => 0,
                    'packages_consignees' => 0,
                    'total_consignees' => 0,
                    'containers_40ft' => 0,
                    'containers_20ft' => 0,
                    'containers_45ft' => 0,
                    'consignees' => [],
                ];
            }

            // Count container types
            if (str_contains($container->container_type, '40')) {
                $agentData[$agentName]['containers_40ft']++;
            } elseif (str_contains($container->container_type, '20')) {
                $agentData[$agentName]['containers_20ft']++;
            } elseif (str_contains($container->container_type, '45')) {
                $agentData[$agentName]['containers_45ft']++;
            }

            // Process HBL packages
            foreach ($container->loaded_packages as $package) {
                $agentData[$agentName]['total_cbm'] += (float) ($package->volume ?? 0);
                $agentData[$agentName]['total_packages']++;

                $packageType = strtolower($package->package_type ?? '');
                if (str_contains($packageType, 'wooden') || str_contains($packageType, 'box')) {
                    $agentData[$agentName]['wooden_boxes']++;
                } elseif (str_contains($packageType, 'steel') || str_contains($packageType, 'trunk')) {
                    $agentData[$agentName]['steel_trunk']++;
                } else {
                    $agentData[$agentName]['other_packages']++;
                }

                // Add consignee from HBL
                if ($package->hbl && $package->hbl->consignee_name) {
                    $agentData[$agentName]['consignees'][$package->hbl->consignee_name] = true;
                }
            }

            $agentData[$agentName]['total_consignees'] = count($agentData[$agentName]['consignees']);
            $agentData[$agentName]['packages_consignees'] = $agentData[$agentName]['total_consignees'];
        }

        $records = array_values(array_map(function ($data) {
            unset($data['consignees']);
            return $data;
        }, $agentData));

        // Apply search filter
        if (!empty($this->filters['search'])) {
            $search = strtolower($this->filters['search']);
            $records = array_filter($records, function ($record) use ($search) {
                return str_contains(strtolower($record['agent_name']), $search);
            });
            $records = array_values($records);
        }

        // Sort by agent name
        usort($records, function ($a, $b) {
            return strcasecmp($a['agent_name'], $b['agent_name']);
        });

        // Calculate statistics
        $this->stats = [
            'total_cbm' => array_sum(array_column($records, 'total_cbm')),
            'wooden_boxes' => array_sum(array_column($records, 'wooden_boxes')),
            'steel_trunk' => array_sum(array_column($records, 'steel_trunk')),
            'other_packages' => array_sum(array_column($records, 'other_packages')),
            'total_packages' => array_sum(array_column($records, 'total_packages')),
            'packages_consignees' => array_sum(array_column($records, 'packages_consignees')),
            'total_consignees' => array_sum(array_column($records, 'total_consignees')),
            'containers_40ft' => array_sum(array_column($records, 'containers_40ft')),
            'containers_20ft' => array_sum(array_column($records, 'containers_20ft')),
            'containers_45ft' => array_sum(array_column($records, 'containers_45ft')),
        ];

        $this->rowCount = count($records);

        return collect($records);
    }

    public function headings(): array
    {
        return [
            ['Laksiri International Freight Forwarders (Pvt) Ltd'],
            ['Agent Wise Container Arrival Summary  ' . $this->getDateRange()],
            [now()->format('m/d/Y') . '  ' . now()->format('h:i:sA'), '', '', '', '', '', '', '', '', 'PAGE - 1'],
            [],
            [
                'Agent Name',
                'CBM',
                'Wooden Boxes',
                'Steel Trunk',
                'Other',
                'Total',
                'No. Of Consig.',
                '40ft',
                '20ft',
                '45ft',
            ],
        ];
    }

    protected function getDateRange(): string
    {
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        if ($dateFrom && $dateTo) {
            return date('d/m/Y', strtotime($dateFrom)) . ' To ' . date('d/m/Y', strtotime($dateTo));
        }

        return 'All Time';
    }

    public function map($row): array
    {
        return [
            $row['agent_name'],
            number_format($row['total_cbm'], 2),
            $row['wooden_boxes'],
            $row['steel_trunk'],
            $row['other_packages'],
            $row['total_packages'],
            $row['total_consignees'],
            $row['containers_40ft'],
            $row['containers_20ft'],
            $row['containers_45ft'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Row 1: Company name - no borders
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Row 2: Report title with date range - no borders
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Row 3: Date/time (left) and page number (right) - no borders
            3 => [
                'font' => ['size' => 10],
            ],
            // Row 5: Column headers - with borders
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
        return 'Agent Summary';
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
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

                // Merge title cells (header rows without borders)
                $sheet->mergeCells('A1:J1');
                $sheet->mergeCells('A2:J2');
                // Don't merge row 3 - we want separate alignment for date/time and page number

                // Set alignment for row 3
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Explicitly remove ALL borders from header rows (1-3) - all cells
                for ($row = 1; $row <= 3; $row++) {
                    for ($col = 'A'; $col <= 'J'; $col++) {
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
                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(12);
                $sheet->getColumnDimension('E')->setWidth(10);
                $sheet->getColumnDimension('F')->setWidth(10);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(8);
                $sheet->getColumnDimension('I')->setWidth(8);
                $sheet->getColumnDimension('J')->setWidth(8);

                // Apply borders to data rows only (starting from row 5)
                $lastRow = 5 + $this->rowCount;
                $sheet->getStyle('A5:J' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Add grand total row
                $totalRow = $lastRow + 1;
                $sheet->setCellValue('A' . $totalRow, '');
                $sheet->setCellValue('B' . $totalRow, number_format($this->stats['total_cbm'], 2));
                $sheet->setCellValue('C' . $totalRow, $this->stats['wooden_boxes']);
                $sheet->setCellValue('D' . $totalRow, $this->stats['steel_trunk']);
                $sheet->setCellValue('E' . $totalRow, $this->stats['other_packages']);
                $sheet->setCellValue('F' . $totalRow, $this->stats['total_packages']);
                $sheet->setCellValue('G' . $totalRow, $this->stats['total_consignees']);
                $sheet->setCellValue('H' . $totalRow, $this->stats['containers_40ft']);
                $sheet->setCellValue('I' . $totalRow, $this->stats['containers_20ft']);
                $sheet->setCellValue('J' . $totalRow, $this->stats['containers_45ft']);

                // Style grand total row
                $sheet->getStyle('A' . $totalRow . ':J' . $totalRow)->applyFromArray([
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
                $sheet->getStyle('B6:J' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
