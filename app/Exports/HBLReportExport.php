<?php

namespace App\Exports;

use App\Models\HBL;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HBLReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
                'branch',
                'container',
                'createdBy',
                'packages',
                'token',
                'verification',
                'cashierPayments',
                'deliver',
                'callFlags'
            ]);

        // Apply the same filters as getData method
        $this->applyFilters($query);

        // Apply sorting
        $sortField = $this->request->input('sort_field', 'created_at');
        $sortOrder = $this->request->input('sort_order', 'desc');
        
        $sortableFields = [
            'reference' => 'reference',
            'hbl_name' => 'hbl_name',
            'cargo_type' => 'cargo_type',
            'hbl_type' => 'hbl_type',
            'created_at' => 'created_at',
            'grand_total' => 'grand_total',
        ];
        
        $dbSortField = $sortableFields[$sortField] ?? 'created_at';
        $query->orderBy($dbSortField, $sortOrder);

        return $query->get();
    }

    /**
     * Apply filters to query (same as controller)
     */
    private function applyFilters($query): void
    {
        // Loaded Date Range
        if ($this->request->filled('loaded_date_from')) {
            $query->whereHas('packages.containerPackages', function ($q) {
                $q->where('loaded_at', '>=', $this->request->input('loaded_date_from'));
            });
        }

        if ($this->request->filled('loaded_date_to')) {
            $query->whereHas('packages.containerPackages', function ($q) {
                $q->where('loaded_at', '<=', $this->request->input('loaded_date_to') . ' 23:59:59');
            });
        }

        // Unloaded/Destuff Date Range
        if ($this->request->filled('unloaded_date_from')) {
            $query->whereHas('packages.containerPackages', function ($q) {
                $q->where('unloaded_at', '>=', $this->request->input('unloaded_date_from'));
            });
        }

        if ($this->request->filled('unloaded_date_to')) {
            $query->whereHas('packages.containerPackages', function ($q) {
                $q->where('unloaded_at', '<=', $this->request->input('unloaded_date_to') . ' 23:59:59');
            });
        }

        // Branch/Agent filter
        if ($this->request->filled('branch_id')) {
            $query->where('branch_id', $this->request->input('branch_id'));
        }

        // Customer search
        if ($this->request->filled('customer_search')) {
            $search = $this->request->input('customer_search');
            $query->where(function ($q) use ($search) {
                $q->where('hbl_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
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
            $query->whereHas('token', function ($q) {
                $q->where('created_at', '>=', $this->request->input('token_issued_date_from'));
            });
        }

        if ($this->request->filled('token_issued_date_to')) {
            $query->whereHas('token', function ($q) {
                $q->where('created_at', '<=', $this->request->input('token_issued_date_to') . ' 23:59:59');
            });
        }

        // Gate Pass Marked Date Range
        if ($this->request->filled('gate_pass_date_from')) {
            $query->whereHas('packages.examination', function ($q) {
                $q->where('gate_pass_marked_at', '>=', $this->request->input('gate_pass_date_from'));
            });
        }

        if ($this->request->filled('gate_pass_date_to')) {
            $query->whereHas('packages.examination', function ($q) {
                $q->where('gate_pass_marked_at', '<=', $this->request->input('gate_pass_date_to') . ' 23:59:59');
            });
        }

        // Cashier Invoice Date Range
        if ($this->request->filled('cashier_invoice_date_from')) {
            $query->whereHas('cashierPayments', function ($q) {
                $q->where('created_at', '>=', $this->request->input('cashier_invoice_date_from'));
            });
        }

        if ($this->request->filled('cashier_invoice_date_to')) {
            $query->whereHas('cashierPayments', function ($q) {
                $q->where('created_at', '<=', $this->request->input('cashier_invoice_date_to') . ' 23:59:59');
            });
        }

        // Document Verified Date Range
        if ($this->request->filled('document_verified_date_from')) {
            $query->whereHas('verification', function ($q) {
                $q->where('verified_at', '>=', $this->request->input('document_verified_date_from'));
            });
        }

        if ($this->request->filled('document_verified_date_to')) {
            $query->whereHas('verification', function ($q) {
                $q->where('verified_at', '<=', $this->request->input('document_verified_date_to') . ' 23:59:59');
            });
        }

        // Shipment/Container filter
        if ($this->request->filled('container_reference')) {
            $query->whereHas('container', function ($q) {
                $q->where('reference', 'like', '%' . $this->request->input('container_reference') . '%');
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
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('hbl_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'HBL Reference',
            'Customer Name',
            'Contact Number',
            'Email',
            'Branch',
            'Container Reference',
            'Cargo Type',
            'HBL Type',
            'Total Packages',
            'Loaded Date',
            'Unloaded Date',
            'Appointment Date',
            'Token Number',
            'Token Issued Date',
            'Document Verified Date',
            'Cashier Invoice Date',
            'Gate Pass Date',
            'Grand Total',
            'Paid Amount',
            'Balance',
            'Created Date',
            'Created By',
        ];
    }

    /**
     * Map data for each row
     */
    public function map($hbl): array
    {
        // Get loaded date (first package loaded)
        $loadedDate = $hbl->packages()
            ->join('container_hbl_package', 'hbl_packages.id', '=', 'container_hbl_package.hbl_package_id')
            ->whereNotNull('container_hbl_package.loaded_at')
            ->orderBy('container_hbl_package.loaded_at', 'asc')
            ->value('container_hbl_package.loaded_at');

        // Get unloaded date (last package unloaded)
        $unloadedDate = $hbl->packages()
            ->join('container_hbl_package', 'hbl_packages.id', '=', 'container_hbl_package.hbl_package_id')
            ->whereNotNull('container_hbl_package.unloaded_at')
            ->orderBy('container_hbl_package.unloaded_at', 'desc')
            ->value('container_hbl_package.unloaded_at');

        return [
            $hbl->reference,
            $hbl->hbl_name,
            $hbl->contact_number,
            $hbl->email,
            $hbl->branch?->name,
            $hbl->container?->reference,
            $hbl->cargo_type,
            $hbl->hbl_type,
            $hbl->packages->count(),
            $loadedDate ? date('Y-m-d H:i:s', strtotime($loadedDate)) : '',
            $unloadedDate ? date('Y-m-d H:i:s', strtotime($unloadedDate)) : '',
            $hbl->callFlags()->latest()->first()?->appointment_date,
            $hbl->token?->token,
            $hbl->token?->created_at?->format('Y-m-d H:i:s'),
            $hbl->verification?->verified_at?->format('Y-m-d H:i:s'),
            $hbl->cashierPayments()->latest()->first()?->created_at?->format('Y-m-d H:i:s'),
            $hbl->packages()->whereHas('examination', function($q) {
                $q->whereNotNull('gate_pass_marked_at');
            })->with('examination')->first()?->examination?->gate_pass_marked_at?->format('Y-m-d H:i:s'),
            number_format($hbl->grand_total, 2),
            number_format($hbl->paid_amount, 2),
            number_format($hbl->grand_total - $hbl->paid_amount, 2),
            $hbl->created_at?->format('Y-m-d H:i:s'),
            $hbl->createdBy?->name,
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ],
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
