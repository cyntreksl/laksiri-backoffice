<?php

namespace App\Exports;

use App\Models\HBLPackage;
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

class HBLPackageReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the collection of packages to export
     */
    public function collection()
    {
        $query = HBLPackage::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->with([
                'hbl:id,hbl_number,hbl_name,contact_number,email,cargo_type,branch_id',
                'hbl.branch:id,name',
                'containers:id,reference',
            ]);

        $this->applyFilters($query);

        $sortField = $this->request->input('sort_field', 'created_at');
        $sortOrder = $this->request->input('sort_order', 'desc');
        
        $sortableFields = [
            'hbl_number' => 'hbl_number',
            'package_number' => 'id',
            'weight' => 'weight',
            'cbm' => 'volume',
            'loaded_date' => 'loaded_at',
            'unloaded_date' => 'unloaded_at',
            'created_at' => 'hbl_packages.created_at',
        ];
        
        $dbSortField = $sortableFields[$sortField] ?? 'hbl_packages.created_at';
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
            $query->where('hbl_packages.loaded_at', '>=', $this->request->input('loaded_date_from'));
        }

        if ($this->request->filled('loaded_date_to')) {
            $query->where('hbl_packages.loaded_at', '<=', $this->request->input('loaded_date_to') . ' 23:59:59');
        }

        if ($this->request->filled('unloaded_date_from')) {
            $query->where('hbl_packages.unloaded_at', '>=', $this->request->input('unloaded_date_from'));
        }

        if ($this->request->filled('unloaded_date_to')) {
            $query->where('hbl_packages.unloaded_at', '<=', $this->request->input('unloaded_date_to') . ' 23:59:59');
        }

        if ($this->request->filled('branch_id')) {
            $query->whereHas('hbl', function ($q) {
                $q->where('branch_id', $this->request->input('branch_id'));
            });
        }

        if ($this->request->filled('customer_search')) {
            $search = $this->request->input('customer_search');
            $query->whereHas('hbl', function ($q) use ($search) {
                $q->where('hbl_name', $search);
            });
        }

        if ($this->request->filled('appointment_date_from')) {
            $query->whereHas('hbl.callFlags', function ($q) {
                $q->where('appointment_date', '>=', $this->request->input('appointment_date_from'));
            });
        }

        if ($this->request->filled('appointment_date_to')) {
            $query->whereHas('hbl.callFlags', function ($q) {
                $q->where('appointment_date', '<=', $this->request->input('appointment_date_to') . ' 23:59:59');
            });
        }

        if ($this->request->filled('token_issued_date_from')) {
            $query->whereHas('hbl.tokens', function ($q) {
                $q->where('created_at', '>=', $this->request->input('token_issued_date_from'));
            });
        }

        if ($this->request->filled('token_issued_date_to')) {
            $query->whereHas('hbl.tokens', function ($q) {
                $q->where('created_at', '<=', $this->request->input('token_issued_date_to') . ' 23:59:59');
            });
        }

        if ($this->request->filled('gate_pass_date_from')) {
            $query->whereHas('hbl.tokens', function ($q) {
                $q->whereHas('examination', function ($subQ) {
                    $subQ->where('is_issued_gate_pass', true)
                        ->where('released_at', '>=', $this->request->input('gate_pass_date_from'));
                });
            });
        }

        if ($this->request->filled('gate_pass_date_to')) {
            $query->whereHas('hbl.tokens', function ($q) {
                $q->whereHas('examination', function ($subQ) {
                    $subQ->where('is_issued_gate_pass', true)
                        ->where('released_at', '<=', $this->request->input('gate_pass_date_to') . ' 23:59:59');
                });
            });
        }

        if ($this->request->filled('cashier_invoice_date_from')) {
            $query->whereHas('hbl.tokens', function ($q) {
                $q->whereHas('cashierPayment', function ($subQ) {
                    $subQ->where('created_at', '>=', $this->request->input('cashier_invoice_date_from'));
                });
            });
        }

        if ($this->request->filled('cashier_invoice_date_to')) {
            $query->whereHas('hbl.tokens', function ($q) {
                $q->whereHas('cashierPayment', function ($subQ) {
                    $subQ->where('created_at', '<=', $this->request->input('cashier_invoice_date_to') . ' 23:59:59');
                });
            });
        }

        if ($this->request->filled('document_verified_date_from')) {
            $query->whereHas('hbl.tokens.verification', function ($q) {
                $q->where('created_at', '>=', $this->request->input('document_verified_date_from'));
            });
        }

        if ($this->request->filled('document_verified_date_to')) {
            $query->whereHas('hbl.tokens.verification', function ($q) {
                $q->where('created_at', '<=', $this->request->input('document_verified_date_to') . ' 23:59:59');
            });
        }

        if ($this->request->filled('container_id')) {
            $query->whereHas('containers', function ($q) {
                $q->where('containers.id', $this->request->input('container_id'));
            });
        }

        if ($this->request->filled('cargo_type')) {
            $query->whereHas('hbl', function ($q) {
                $q->where('cargo_type', $this->request->input('cargo_type'));
            });
        }

        if ($this->request->filled('search')) {
            $search = $this->request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('hbl_packages.id', 'like', "%{$search}%")
                    ->orWhere('hbl_packages.remarks', 'like', "%{$search}%")
                    ->orWhereHas('hbl', function ($subQ) use ($search) {
                        $subQ->where('hbl_number', 'like', "%{$search}%")
                            ->orWhere('hbl_name', 'like', "%{$search}%")
                            ->orWhere('contact_number', 'like', "%{$search}%");
                    });
            });
        }
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'Package ID',
            'HBL Number',
            'Customer Name',
            'Customer Contact',
            'Customer Email',
            'Branch/Agent',
            'Cargo Type',
            'Container Reference',
            'Description',
            'Weight (KG)',
            'CBM',
            'Loaded Date',
            'Unloaded Date',
            'Created Date',
        ];
    }

    /**
     * Map data for each row
     */
    public function map($package): array
    {
        $container = $package->containers->first();
        
        return [
            'PKG-' . str_pad($package->id, 6, '0', STR_PAD_LEFT),
            $package->hbl?->hbl_number ?? 'N/A',
            $package->hbl?->hbl_name ?? 'N/A',
            $package->hbl?->contact_number ?? '',
            $package->hbl?->email ?? '',
            $package->hbl?->branch?->name ?? 'N/A',
            $package->hbl?->cargo_type ?? '',
            $container?->reference ?? '',
            $package->remarks ?? '',
            number_format($package->weight ?? 0, 2),
            number_format($package->volume ?? 0, 2),
            $package->loaded_at ? $package->loaded_at->format('Y-m-d H:i:s') : '',
            $package->unloaded_at ? $package->unloaded_at->format('Y-m-d H:i:s') : '',
            $package->created_at?->format('Y-m-d H:i:s') ?? '',
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
            'A' => 15, // Package ID
            'B' => 15, // HBL Number
            'C' => 25, // Customer Name
            'D' => 15, // Customer Contact
            'E' => 25, // Customer Email
            'F' => 20, // Branch/Agent
            'G' => 12, // Cargo Type
            'H' => 18, // Container Reference
            'I' => 30, // Description
            'J' => 12, // Weight
            'K' => 10, // CBM
            'L' => 18, // Loaded Date
            'M' => 18, // Unloaded Date
            'N' => 18, // Created Date
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
                
                $rightColumns = ['J', 'K'];
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
        return 'HBL Package Report';
    }
}
