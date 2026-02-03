<?php

namespace App\Exports;

use App\Models\HBL;
use App\Models\Examination;
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
                'user',
                'packages.containers',
                'tokens.verification',
                'tokens.cashierPayment',
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
                $q->where('verified_at', '>=', $this->request->input('document_verified_date_from'));
            });
        }

        if ($this->request->filled('document_verified_date_to')) {
            $query->whereHas('tokens.verification', function ($q) {
                $q->where('verified_at', '<=', $this->request->input('document_verified_date_to') . ' 23:59:59');
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
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'HBL Reference',
            'HBL Number',
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
            ->whereNotNull('loaded_at')
            ->orderBy('loaded_at', 'asc')
            ->value('loaded_at');

        // Get unloaded date (last package unloaded)
        $unloadedDate = $hbl->packages()
            ->whereNotNull('unloaded_at')
            ->orderBy('unloaded_at', 'desc')
            ->value('unloaded_at');

        // Get container reference through packages
        $containerReference = '';
        if ($hbl->packages->isNotEmpty()) {
            $firstPackage = $hbl->packages->first();
            $container = $firstPackage->containers()->withoutGlobalScopes()->first() 
                ?? $firstPackage->duplicate_containers()->withoutGlobalScopes()->first();
            $containerReference = $container?->reference ?? '';
        }

        return [
            $hbl->reference,
            $hbl->hbl_number,
            $hbl->hbl_name,
            $hbl->contact_number,
            $hbl->email,
            $hbl->branch?->name,
            $containerReference,
            $hbl->cargo_type,
            $hbl->hbl_type,
            $hbl->packages->count(),
            $loadedDate ? date('Y-m-d H:i:s', strtotime($loadedDate)) : '',
            $unloadedDate ? date('Y-m-d H:i:s', strtotime($unloadedDate)) : '',
            $hbl->callFlags()->latest()->first()?->appointment_date,
            $hbl->tokens->first()?->token,
            $hbl->tokens->first()?->created_at?->format('Y-m-d H:i:s'),
            $hbl->tokens->first()?->verification?->verified_at?->format('Y-m-d H:i:s'),
            $hbl->tokens->first()?->cashierPayment?->created_at?->format('Y-m-d H:i:s'),
            Examination::where('hbl_id', $hbl->id)
                ->where('is_issued_gate_pass', true)
                ->whereNotNull('released_at')
                ->latest('released_at')
                ->value('released_at') 
                ? date('Y-m-d H:i:s', strtotime(Examination::where('hbl_id', $hbl->id)
                    ->where('is_issued_gate_pass', true)
                    ->whereNotNull('released_at')
                    ->latest('released_at')
                    ->value('released_at'))) 
                : '',
            number_format($hbl->grand_total, 2),
            number_format($hbl->paid_amount, 2),
            number_format($hbl->grand_total - $hbl->paid_amount, 2),
            $hbl->created_at?->format('Y-m-d H:i:s'),
            $hbl->user?->name,
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
