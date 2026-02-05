<?php

namespace App\Exports;

use App\Models\HBL;
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

class HBLReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the collection of HBLs to export
     */
    public function collection()
    {
        $query = HBL::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->with([
                'branch:id,name',
                'user:id,name',
                'packages:id,hbl_id',
                'latestDetainRecord' => function ($query) {
                    $query->select('detain_records.id', 'detain_records.rtfable_id', 'detain_records.rtfable_type', 'detain_records.is_rtf', 'detain_records.detain_type');
                }
            ]);

        // Apply the same filters as getData method
        $this->applyFilters($query);

        // Apply sorting
        $sortField = $this->request->input('sort_field', 'created_at');
        $sortOrder = $this->request->input('sort_order', 'desc');
        
        $sortableFields = [
            'reference' => 'reference',
            'hbl_number' => 'hbl_number',
            'hbl_name' => 'hbl_name',
            'cargo_type' => 'cargo_type',
            'hbl_type' => 'hbl_type',
            'created_at' => 'created_at',
            'grand_total' => 'grand_total',
        ];
        
        $dbSortField = $sortableFields[$sortField] ?? 'created_at';
        $query->orderBy($dbSortField, $sortOrder);

        // Limit to same as PDF (500 records)
        $limit = $this->request->input('limit', 500);
        $limit = min($limit, 500);

        return $query->limit($limit)->get();
    }

    /**
     * Apply filters to query (same as controller)
     */
    private function applyFilters($query): void
    {
        // Loaded Date Range
        if ($this->request->filled('loaded_date_from')) {
            $query->whereHas('packages', function ($q) {
                $q->where('loaded_at', '>=', $this->request->input('loaded_date_from'));
            });
        }

        if ($this->request->filled('loaded_date_to')) {
            $query->whereHas('packages', function ($q) {
                $q->where('loaded_at', '<=', $this->request->input('loaded_date_to') . ' 23:59:59');
            });
        }

        // Unloaded/Destuff Date Range
        if ($this->request->filled('unloaded_date_from')) {
            $query->whereHas('packages', function ($q) {
                $q->where('unloaded_at', '>=', $this->request->input('unloaded_date_from'));
            });
        }

        if ($this->request->filled('unloaded_date_to')) {
            $query->whereHas('packages', function ($q) {
                $q->where('unloaded_at', '<=', $this->request->input('unloaded_date_to') . ' 23:59:59');
            });
        }

        // Branch/Agent filter
        if ($this->request->filled('branch_id')) {
            $query->where('branch_id', $this->request->input('branch_id'));
        }

        // Customer search (HBL name, contact, email)
        if ($this->request->filled('customer_search')) {
            $search = $this->request->input('customer_search');
            $query->where('hbl_name', $search);
        }

        // Appointment Date Range
        if ($this->request->filled('appointment_date_from')) {
            $query->whereHas('callFlags', function ($q) {
                $q->where('appointment_date', '>=', $this->request->input('appointment_date_from'));
            });
        }

        if ($this->request->filled('appointment_date_to')) {
            $query->whereHas('callFlags', function ($q) {
                $q->where('appointment_date', '<=', $this->request->input('appointment_date_to') . ' 23:59:59');
            });
        }

        // Token Issued Date Range
        if ($this->request->filled('token_issued_date_from')) {
            $query->whereHas('tokens', function ($q) {
                $q->where('created_at', '>=', $this->request->input('token_issued_date_from'));
            });
        }

        if ($this->request->filled('token_issued_date_to')) {
            $query->whereHas('tokens', function ($q) {
                $q->where('created_at', '<=', $this->request->input('token_issued_date_to') . ' 23:59:59');
            });
        }

        // Gate Pass Marked Date Range
        if ($this->request->filled('gate_pass_date_from')) {
            $query->whereHas('tokens', function ($q) {
                $q->whereHas('examination', function ($subQ) {
                    $subQ->where('is_issued_gate_pass', true)
                        ->where('released_at', '>=', $this->request->input('gate_pass_date_from'));
                });
            });
        }

        if ($this->request->filled('gate_pass_date_to')) {
            $query->whereHas('tokens', function ($q) {
                $q->whereHas('examination', function ($subQ) {
                    $subQ->where('is_issued_gate_pass', true)
                        ->where('released_at', '<=', $this->request->input('gate_pass_date_to') . ' 23:59:59');
                });
            });
        }

        // Cashier Invoice Date Range
        if ($this->request->filled('cashier_invoice_date_from')) {
            $query->whereHas('tokens', function ($q) {
                $q->whereHas('cashierPayment', function ($subQ) {
                    $subQ->where('created_at', '>=', $this->request->input('cashier_invoice_date_from'));
                });
            });
        }

        if ($this->request->filled('cashier_invoice_date_to')) {
            $query->whereHas('tokens', function ($q) {
                $q->whereHas('cashierPayment', function ($subQ) {
                    $subQ->where('created_at', '<=', $this->request->input('cashier_invoice_date_to') . ' 23:59:59');
                });
            });
        }

        // Document Verified Date Range
        if ($this->request->filled('document_verified_date_from')) {
            $query->whereHas('tokens.verification', function ($q) {
                $q->where('created_at', '>=', $this->request->input('document_verified_date_from'));
            });
        }

        if ($this->request->filled('document_verified_date_to')) {
            $query->whereHas('tokens.verification', function ($q) {
                $q->where('created_at', '<=', $this->request->input('document_verified_date_to') . ' 23:59:59');
            });
        }

        // Shipment/Container filter
        if ($this->request->filled('container_id')) {
            $query->whereHas('packages.containers', function ($q) {
                $q->where('containers.id', $this->request->input('container_id'));
            });
        }

        // Cargo Type filter
        if ($this->request->filled('cargo_type')) {
            $query->where('cargo_type', $this->request->input('cargo_type'));
        }

        // HBL Type filter
        if ($this->request->filled('hbl_type')) {
            $query->where('hbl_type', $this->request->input('hbl_type'));
        }

        // General search
        if ($this->request->filled('search')) {
            $search = $this->request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('hbl_number', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('hbl_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Define column headings - matching PDF columns
     */
    public function headings(): array
    {
        return [
            'HBL Number',
            'Reference',
            'Customer Name',
            'Customer Email',
            'Customer Contact',
            'Consignee Name',
            'Consignee Contact',
            'Branch/Agent',
            'Warehouse',
            'Cargo Type',
            'HBL Type',
            'Total Packages',
            'Loaded Date',
            'Unloaded Date',
            'Freight Charge',
            'DO Charge',
            'Grand Total',
            'Paid Amount',
            'Balance',
            'Status',
            'Is Detained',
            'Is Short Loaded',
            'Created Date',
            'Created By',
        ];
    }

    /**
     * Map data for each row - matching PDF columns
     */
    public function map($hbl): array
    {
        // Get loaded date (first package loaded)
        $loadedDate = $hbl->packages()
            ->whereNotNull('loaded_at')
            ->orderBy('loaded_at', 'asc')
            ->value('loaded_at');

        // Get unloaded date (last package unloaded)
        $unloadedDate = $hbl->packages()
            ->whereNotNull('unloaded_at')
            ->orderBy('unloaded_at', 'desc')
            ->value('unloaded_at');

        // Calculate balance
        $balance = $hbl->grand_total - $hbl->paid_amount;

        // Check status
        $isDetained = $hbl->latestDetainRecord?->is_rtf ?? false;
        $isShortLoaded = $hbl->is_short_loading ?? false;

        // Determine status
        $status = 'Active';
        if ($isDetained) {
            $status = 'Detained';
        } elseif ($isShortLoaded) {
            $status = 'Short Loaded';
        } elseif ($hbl->is_released) {
            $status = 'Released';
        }

        return [
            $hbl->hbl_number ?? $hbl->hbl ?? 'N/A',
            $hbl->reference ?? '',
            $hbl->hbl_name ?? 'N/A',
            $hbl->email ?? '',
            $hbl->contact_number ?? '',
            $hbl->consignee_name ?? 'N/A',
            $hbl->consignee_contact ?? '',
            $hbl->branch?->name ?? 'N/A',
            $hbl->warehouse ?? '',
            $hbl->cargo_type ?? '',
            $hbl->hbl_type ?? '',
            $hbl->packages->count(),
            $loadedDate ? date('Y-m-d H:i:s', strtotime($loadedDate)) : '',
            $unloadedDate ? date('Y-m-d H:i:s', strtotime($unloadedDate)) : '',
            number_format($hbl->freight_charge ?? 0, 2),
            number_format($hbl->do_charge ?? 0, 2),
            number_format($hbl->grand_total, 2),
            number_format($hbl->paid_amount, 2),
            number_format($balance, 2),
            $status,
            $isDetained ? 'Yes' : 'No',
            $isShortLoaded ? 'Yes' : 'No',
            $hbl->created_at?->format('Y-m-d H:i:s') ?? '',
            $hbl->user?->name ?? '',
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '34495E'] // Dark blue-gray
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Set column widths for better readability
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15, // HBL Number
            'B' => 15, // Reference
            'C' => 25, // Customer Name
            'D' => 25, // Customer Email
            'E' => 15, // Customer Contact
            'F' => 25, // Consignee Name
            'G' => 15, // Consignee Contact
            'H' => 20, // Branch/Agent
            'I' => 15, // Warehouse
            'J' => 12, // Cargo Type
            'K' => 15, // HBL Type
            'L' => 12, // Total Packages
            'M' => 18, // Loaded Date
            'N' => 18, // Unloaded Date
            'O' => 15, // Freight Charge
            'P' => 12, // DO Charge
            'Q' => 15, // Grand Total
            'R' => 15, // Paid Amount
            'S' => 15, // Balance
            'T' => 15, // Status
            'U' => 12, // Is Detained
            'V' => 15, // Is Short Loaded
            'W' => 18, // Created Date
            'X' => 20, // Created By
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
                
                // Get the highest row and column
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Apply borders to all cells
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->setColor(new Color('CCCCCC'));
                
                // Freeze header row
                $sheet->freezePane('A2');
                
                // Auto-filter on header row
                $sheet->setAutoFilter('A1:' . $highestColumn . '1');
                
                // Alternate row colors for better readability
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->setStartColor(new Color('F8F9FA'));
                    }
                }
                
                // Center align specific columns
                $centerColumns = ['L', 'U', 'V']; // Packages, Is Detained, Is Short Loaded
                foreach ($centerColumns as $col) {
                    $sheet->getStyle($col . '2:' . $col . $highestRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                
                // Right align numeric columns
                $rightColumns = ['O', 'P', 'Q', 'R', 'S']; // Charges and amounts
                foreach ($rightColumns as $col) {
                    $sheet->getStyle($col . '2:' . $col . $highestRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                
                // Highlight detained rows in light red
                for ($row = 2; $row <= $highestRow; $row++) {
                    $isDetained = $sheet->getCell('U' . $row)->getValue();
                    if ($isDetained === 'Yes') {
                        $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->setStartColor(new Color('FFEBEE')); // Light red
                    }
                }
                
                // Highlight short loaded rows in light orange
                for ($row = 2; $row <= $highestRow; $row++) {
                    $isShortLoaded = $sheet->getCell('V' . $row)->getValue();
                    $isDetained = $sheet->getCell('U' . $row)->getValue();
                    if ($isShortLoaded === 'Yes' && $isDetained !== 'Yes') {
                        $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->setStartColor(new Color('FFF3E0')); // Light orange
                    }
                }
            },
        ];
    }

    /**
     * Set the worksheet title
     */
    public function title(): string
    {
        return 'HBL Report';
    }
}
