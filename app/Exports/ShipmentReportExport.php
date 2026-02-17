<?php

namespace App\Exports;

use App\Models\Container;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ShipmentReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the collection of containers to export
     */
    public function collection()
    {
        $query = Container::withoutGlobalScope(BranchScope::class)
            ->with([
                'branch:id,name',
                'warehouse:id,name',
            ]);

        $this->applyFilters($query);

        $sortField = $this->request->input('sort_field', 'created_at');
        $sortOrder = $this->request->input('sort_order', 'desc');
        
        $sortableFields = [
            'reference' => 'reference',
            'cargo_type' => 'cargo_type',
            'status' => 'status',
            'loading_started_at' => 'loading_started_at',
            'unloading_started_at' => 'unloading_started_at',
            'reached_date' => 'reached_date',
            'arrived_at_primary_warehouse' => 'arrived_at_primary_warehouse',
            'created_at' => 'created_at',
        ];
        
        $dbSortField = $sortableFields[$sortField] ?? 'created_at';
        $query->orderBy($dbSortField, $sortOrder);

        $limit = $this->request->input('limit', 500);
        $limit = min($limit, 500);

        return $query->limit($limit)->get();
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query): void
    {
        if ($this->request->filled('loaded_date_from')) {
            $query->where('loading_started_at', '>=', $this->request->input('loaded_date_from'));
        }

        if ($this->request->filled('loaded_date_to')) {
            $query->where('loading_started_at', '<=', $this->request->input('loaded_date_to') . ' 23:59:59');
        }

        if ($this->request->filled('unloaded_date_from')) {
            $query->where('unloading_started_at', '>=', $this->request->input('unloaded_date_from'));
        }

        if ($this->request->filled('unloaded_date_to')) {
            $query->where('unloading_started_at', '<=', $this->request->input('unloaded_date_to') . ' 23:59:59');
        }

        if ($this->request->filled('reached_date_from')) {
            $query->where('reached_date', '>=', $this->request->input('reached_date_from'));
        }

        if ($this->request->filled('reached_date_to')) {
            $query->where('reached_date', '<=', $this->request->input('reached_date_to') . ' 23:59:59');
        }

        if ($this->request->filled('arrival_date_from')) {
            $query->where('arrived_at_primary_warehouse', '>=', $this->request->input('arrival_date_from'));
        }

        if ($this->request->filled('arrival_date_to')) {
            $query->where('arrived_at_primary_warehouse', '<=', $this->request->input('arrival_date_to') . ' 23:59:59');
        }

        if ($this->request->filled('release_date_from')) {
            $query->where('departed_at_primary_warehouse', '>=', $this->request->input('release_date_from'));
        }

        if ($this->request->filled('release_date_to')) {
            $query->where('departed_at_primary_warehouse', '<=', $this->request->input('release_date_to') . ' 23:59:59');
        }

        if ($this->request->filled('branch_id')) {
            $query->where('branch_id', $this->request->input('branch_id'));
        }

        if ($this->request->filled('cargo_type')) {
            $query->where('cargo_type', $this->request->input('cargo_type'));
        }

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->input('status'));
        }

        if ($this->request->filled('search')) {
            $search = $this->request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('container_number', 'like', "%{$search}%")
                    ->orWhere('bl_number', 'like', "%{$search}%")
                    ->orWhere('awb_number', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'Reference',
            'Cargo Type',
            'Status',
            'Container Number',
            'BL Number',
            'AWB Number',
            'Branch/Agent',
            'Warehouse',
            'Total Packages',
            'Loaded Date',
            'Loading Ended',
            'Unloaded Date',
            'Unloading Ended',
            'Reached Date',
            'Arrival Date',
            'Release Date',
            'Created Date',
        ];
    }

    /**
     * Map data for each row
     */
    public function map($container): array
    {
        // Get package count
        $packageCount = \DB::table('container_hbl_package')
            ->where('container_id', $container->id)
            ->count();
        
        return [
            $container->reference ?? 'N/A',
            $container->cargo_type ?? 'N/A',
            $container->status ?? 'N/A',
            $container->container_number ?? '',
            $container->bl_number ?? '',
            $container->awb_number ?? '',
            $container->branch?->name ?? 'N/A',
            $container->warehouse?->name ?? 'N/A',
            $packageCount,
            $container->loading_started_at ? $container->loading_started_at->format('Y-m-d H:i:s') : '',
            $container->loading_ended_at ? $container->loading_ended_at->format('Y-m-d H:i:s') : '',
            $container->unloading_started_at ? $container->unloading_started_at->format('Y-m-d H:i:s') : '',
            $container->unloading_ended_at ? $container->unloading_ended_at->format('Y-m-d H:i:s') : '',
            $container->reached_date ? $container->reached_date->format('Y-m-d') : '',
            $container->arrived_at_primary_warehouse ? $container->arrived_at_primary_warehouse->format('Y-m-d H:i:s') : '',
            $container->departed_at_primary_warehouse ? $container->departed_at_primary_warehouse->format('Y-m-d H:i:s') : '',
            $container->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '34495E']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 18, // Reference
            'B' => 12, // Cargo Type
            'C' => 15, // Status
            'D' => 18, // Container Number
            'E' => 15, // BL Number
            'F' => 15, // AWB Number
            'G' => 20, // Branch/Agent
            'H' => 20, // Warehouse
            'I' => 12, // Total Packages
            'J' => 18, // Loaded Date
            'K' => 18, // Loading Ended
            'L' => 18, // Unloaded Date
            'M' => 18, // Unloading Ended
            'N' => 15, // Reached Date
            'O' => 18, // Arrival Date
            'P' => 18, // Release Date
            'Q' => 18, // Created Date
        ];
    }

    /**
     * Register events for additional formatting
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->setColor(new Color('CCCCCC'));
                
                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:' . $highestColumn . '1');
                
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->setStartColor(new Color('F8F9FA'));
                    }
                }
                
                $rightColumns = ['I'];
                foreach ($rightColumns as $col) {
                    $sheet->getStyle($col . '2:' . $col . $highestRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }

    /**
     * Set the worksheet title
     */
    public function title(): string
    {
        return 'Shipment Report';
    }
}
