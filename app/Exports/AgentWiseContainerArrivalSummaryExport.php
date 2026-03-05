<?php

namespace App\Exports;

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
            ->with(['branch', 'hbl_packages.hbl'])
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
            if ($container->container_type === '40ft') {
                $agentData[$agentName]['containers_40ft']++;
            } elseif ($container->container_type === '20ft') {
                $agentData[$agentName]['containers_20ft']++;
            } elseif ($container->container_type === '45ft') {
                $agentData[$agentName]['containers_45ft']++;
            }

            // Process HBL packages
            foreach ($container->hbl_packages as $package) {
                $agentData[$agentName]['total_cbm'] += (float) ($package->cbm ?? 0);
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
            [now()->format('m/d/Y') . '  ' . now()->format('h:i:sA') . '  PAGE - 1'],
            [],
            [
                'Agent Name',
                'CBM',
                'Wooden Boxes',
                'Steel Trunk',
                'Other',
                'Total',
                'No. Of Consig.',
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
            $row['packages_consignees'],
            $row['total_consignees'],
            $row['containers_40ft'],
            $row['containers_20ft'],
            $row['containers_45ft'],
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
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            ],
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

                // Merge title cells
                $sheet->mergeCells('A1:K1');
                $sheet->mergeCells('A2:K2');
                $sheet->mergeCells('A3:K3');

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(12);
                $sheet->getColumnDimension('E')->setWidth(10);
                $sheet->getColumnDimension('F')->setWidth(10);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(8);
                $sheet->getColumnDimension('J')->setWidth(8);
                $sheet->getColumnDimension('K')->setWidth(8);

                // Apply borders to data rows
                $lastRow = 5 + $this->rowCount;
                $sheet->getStyle('A5:K' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Add grand total row
                $totalRow = $lastRow + 1;
                $sheet->setCellValue('A' . $totalRow, 'Grand Total:');
                $sheet->setCellValue('B' . $totalRow, number_format($this->stats['total_cbm'], 2));
                $sheet->setCellValue('C' . $totalRow, $this->stats['wooden_boxes']);
                $sheet->setCellValue('D' . $totalRow, $this->stats['steel_trunk']);
                $sheet->setCellValue('E' . $totalRow, $this->stats['other_packages']);
                $sheet->setCellValue('F' . $totalRow, $this->stats['total_packages']);
                $sheet->setCellValue('G' . $totalRow, $this->stats['packages_consignees']);
                $sheet->setCellValue('H' . $totalRow, $this->stats['total_consignees']);
                $sheet->setCellValue('I' . $totalRow, $this->stats['containers_40ft']);
                $sheet->setCellValue('J' . $totalRow, $this->stats['containers_20ft']);
                $sheet->setCellValue('K' . $totalRow, $this->stats['containers_45ft']);

                // Style grand total row
                $sheet->getStyle('A' . $totalRow . ':K' . $totalRow)->applyFromArray([
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
                $sheet->getStyle('B6:K' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
