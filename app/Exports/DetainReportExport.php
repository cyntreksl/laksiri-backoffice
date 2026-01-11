<?php

namespace App\Exports;

use App\Models\DetainRecord;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetainReportExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Shipment Reference',
            'HBL Reference',
            'Package Number',
            'Entity Level',
            'Detain Type',
            'Detain Reason',
            'Detained Date',
            'Detention Duration',
            'Status',
            'Released Date',
            'Lift Reason',
            'Remarks',
            'Detained By',
            'Lifted By',
            'Branch',
        ];
    }

    public function map($record): array
    {
        $entityData = $this->getEntityData($record);
        $duration = $this->calculateDuration($record);

        return [
            $record->id,
            $entityData['shipment_reference'] ?? 'N/A',
            $entityData['hbl_reference'] ?? 'N/A',
            $entityData['package_number'] ?? 'N/A',
            ucfirst($record->entity_level ?? 'N/A'),
            $record->detain_type ?? 'N/A',
            $record->detain_reason ?? 'N/A',
            $record->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $this->formatDuration($duration),
            $record->action === 'detain' ? 'Detained' : 'Released',
            $record->lifted_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $record->lift_reason ?? 'N/A',
            $record->remarks ?? 'N/A',
            $record->detainedBy?->name ?? 'N/A',
            $record->liftedBy?->name ?? 'N/A',
            $record->branch?->name ?? 'N/A',
        ];
    }

    private function getEntityData($record): array
    {
        $data = [
            'shipment_reference' => null,
            'hbl_reference' => null,
            'package_number' => null,
        ];

        if (!$record->rtfable) {
            return $data;
        }

        try {
            switch ($record->rtfable_type) {
                case 'App\Models\Container':
                    $data['shipment_reference'] = $record->rtfable->reference ?? null;
                    break;

                case 'App\Models\HBL':
                    $hbl = $record->rtfable;
                    $data['hbl_reference'] = $hbl->reference ?? null;
                    // Load container relationship if not loaded
                    if ($hbl->relationLoaded('container')) {
                        $data['shipment_reference'] = $hbl->container->reference ?? null;
                    } else {
                        $container = $hbl->container()->first();
                        $data['shipment_reference'] = $container->reference ?? null;
                    }
                    break;

                case 'App\Models\HBLPackage':
                    $package = $record->rtfable;
                    $data['package_number'] = $package->package_number ?? null;
                    
                    // Load HBL relationship if not loaded
                    if ($package->relationLoaded('hbl')) {
                        $hbl = $package->hbl;
                    } else {
                        $hbl = $package->hbl()->first();
                    }
                    
                    if ($hbl) {
                        $data['hbl_reference'] = $hbl->reference ?? null;
                        
                        // Load container relationship if not loaded
                        if ($hbl->relationLoaded('container')) {
                            $data['shipment_reference'] = $hbl->container->reference ?? null;
                        } else {
                            $container = $hbl->container()->first();
                            $data['shipment_reference'] = $container->reference ?? null;
                        }
                    }
                    break;
            }
        } catch (\Exception $e) {
            // If any error occurs, just return the data as is
            \Log::error('Error getting entity data for detain record: ' . $e->getMessage());
        }

        return $data;
    }

    private function calculateDuration($record): ?int
    {
        if ($record->action !== 'detain') {
            return null;
        }

        $startDate = $record->created_at;
        $endDate = $record->lifted_at ?? now();

        return $startDate->diffInMinutes($endDate);
    }

    private function formatDuration(?int $minutes): string
    {
        if ($minutes === null) {
            return 'N/A';
        }

        $days = floor($minutes / 1440);
        $hours = floor(($minutes % 1440) / 60);
        $mins = $minutes % 60;

        $parts = [];
        if ($days > 0) $parts[] = "{$days}d";
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($mins > 0 || empty($parts)) $parts[] = "{$mins}m";

        return implode(' ', $parts);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function query()
    {
        $query = DetainRecord::query()
            ->with(['detainedBy', 'liftedBy', 'rtfable', 'branch']);

        $this->applyFilters($query);

        // Apply sorting
        $sortField = $this->request->input('sort_field', 'created_at');
        $sortOrder = $this->request->input('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        return $query;
    }

    private function applyFilters($query): void
    {
        // Only apply filters if they are actually set and not null
        if ($this->request->filled('date_from') && $this->request->input('date_from') !== null) {
            $query->where('created_at', '>=', $this->request->input('date_from'));
        }

        if ($this->request->filled('date_to') && $this->request->input('date_to') !== null) {
            $query->where('created_at', '<=', $this->request->input('date_to') . ' 23:59:59');
        }

        if ($this->request->filled('status') && $this->request->input('status') !== null) {
            $status = $this->request->input('status');
            if ($status === 'detained') {
                $query->where('action', 'detain')->where('is_rtf', true);
            } elseif ($status === 'released') {
                $query->where('action', 'lift');
            }
        }

        if ($this->request->filled('detain_type') && $this->request->input('detain_type') !== null) {
            $query->where('detain_type', $this->request->input('detain_type'));
        }

        if ($this->request->filled('entity_level') && $this->request->input('entity_level') !== null) {
            $query->where('entity_level', $this->request->input('entity_level'));
        }

        if ($this->request->filled('search') && $this->request->input('search') !== null) {
            $search = $this->request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('detain_reason', 'like', "%{$search}%")
                    ->orWhere('lift_reason', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('branch_id') && $this->request->input('branch_id') !== null) {
            $query->where('branch_id', $this->request->input('branch_id'));
        }
    }
}
