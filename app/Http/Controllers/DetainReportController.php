<?php

namespace App\Http\Controllers;

use App\Models\DetainRecord;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLPackage;
use App\Exports\DetainReportExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class DetainReportController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display the detain report page
     */
    public function index(): Response
    {
        $this->authorize('reports.detain');

        return Inertia::render('Reports/DetainReport', [
            'detainTypes' => $this->getDetainTypes(),
            'entityLevels' => $this->getEntityLevels(),
        ]);
    }

    /**
     * Get available detain types
     */
    private function getDetainTypes(): array
    {
        return [
            ['value' => 'RTF', 'label' => 'RTF'],
            ['value' => 'DDC', 'label' => 'DDC'],
            ['value' => 'SDDC', 'label' => 'SDDC'],
            ['value' => 'IAU', 'label' => 'IAU'],
            ['value' => 'DC', 'label' => 'DC'],
            ['value' => 'CO', 'label' => 'CO '],
            ['value' => 'ICT', 'label' => 'ICT'],
        ];
    }

    /**
     * Get available entity levels
     */
    private function getEntityLevels(): array
    {
        return [
            ['value' => 'shipment', 'label' => 'Shipment'],
            ['value' => 'hbl', 'label' => 'HBL'],
            ['value' => 'package', 'label' => 'Package'],
        ];
    }

    /**
     * Get detain report data with filters
     */
    public function getData(Request $request): JsonResponse
    {
        $this->authorize('reports.detain');

        $query = DetainRecord::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->with(['detainedBy', 'liftedBy', 'branch']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Get total count before pagination
        $totalRecords = $query->count();
        
        // Calculate stats for summary cards
        $statsQuery = DetainRecord::withoutGlobalScope(\App\Models\Scopes\BranchScope::class);
        $this->applyFilters($statsQuery, $request);
        
        $detainedCount = (clone $statsQuery)->where('action', 'detain')->count();
        $releasedCount = (clone $statsQuery)->where('action', 'lift')->count();

        // Apply sorting - handle computed fields
        $sortField = $request->input('sort_field', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        // Map frontend field names to database columns
        $sortableFields = [
            'id' => 'id',
            'shipment_reference' => 'rtfable_id',
            'hbl_reference' => 'rtfable_id',
            'package_number' => 'rtfable_id',
            'entity_level' => 'entity_level',
            'detain_type' => 'detain_type',
            'detained_date' => 'created_at',
            'detention_duration_human' => 'created_at',
            'status' => 'action',
            'released_date' => 'lifted_at',
        ];
        
        $dbSortField = $sortableFields[$sortField] ?? 'created_at';
        $query->orderBy($dbSortField, $sortOrder);

        // Apply pagination
        $perPage = $request->input('per_page', 25);
        $page = $request->input('page', 1);
        $records = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        // Load polymorphic relationships conditionally
        $records->load([
            'rtfable' => function ($query) {
                // Load even soft-deleted entities
                $query->withTrashed();
            }
        ]);

        // Load nested relationships based on type
        foreach ($records as $record) {
            if (!$record->rtfable) {
                continue;
            }
            
            if ($record->rtfable_type === 'App\Models\Container') {
                // Container doesn't need additional relationships
            } elseif ($record->rtfable_type === 'App\Models\HBL') {
                $record->rtfable->load('containers');
            } elseif ($record->rtfable_type === 'App\Models\HBLPackage') {
                $record->rtfable->load(['hbl' => function ($query) {
                    $query->withTrashed();
                }]);
                
                if ($record->rtfable->hbl) {
                    $record->rtfable->hbl->load('containers');
                }
                
                $record->rtfable->load('containers');
            }
        }

        // Transform data
        $data = $records->map(function ($record) {
            return $this->transformRecord($record);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $totalRecords,
            'detained_count' => $detainedCount,
            'released_count' => $releasedCount,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($totalRecords / $perPage),
        ]);
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {
        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        // Status filter (detained/released)
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'detained') {
                $query->where('action', 'detain')->where('is_rtf', true);
            } elseif ($status === 'released') {
                $query->where('action', 'lift');
            }
        }

        // Detain type filter
        if ($request->filled('detain_type')) {
            $query->where('detain_type', $request->input('detain_type'));
        }

        // Entity level filter
        if ($request->filled('entity_level')) {
            $query->where('entity_level', $request->input('entity_level'));
        }

        // Detain reason search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('detain_reason', 'like', "%{$search}%")
                    ->orWhere('lift_reason', 'like', "%{$search}%")
                    ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        // Branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }
    }

    /**
     * Transform detain record for response
     */
    private function transformRecord(DetainRecord $record): array
    {
        $entityData = $this->getEntityData($record);
        $detentionDuration = $this->calculateDuration($record);

        // If this is a lift action, find the corresponding detain record
        $detainRecord = null;
        if ($record->action === 'lift') {
            $detainRecord = DetainRecord::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->where('rtfable_type', $record->rtfable_type)
                ->where('rtfable_id', $record->rtfable_id)
                ->where('action', 'detain')
                ->where('detain_type', $record->detain_type)
                ->where('created_at', '<=', $record->created_at)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        return [
            'id' => $record->id,
            'shipment_reference' => $entityData['shipment_reference'],
            'hbl_reference' => $entityData['hbl_reference'],
            'package_number' => $entityData['package_number'],
            'entity_level' => $record->entity_level,
            'entity_type' => $entityData['entity_type'],
            'detain_type' => $record->detain_type,
            'detain_reason' => $record->action === 'detain' ? $record->detain_reason : ($detainRecord?->detain_reason ?? 'N/A'),
            'lift_reason' => $record->lift_reason,
            'remarks' => $record->remarks,
            'action' => $record->action,
            'status' => $record->action === 'detain' ? 'Detained' : 'Released',
            'detained_date' => $record->action === 'detain' ? $record->created_at?->format('Y-m-d H:i:s') : ($detainRecord?->created_at?->format('Y-m-d H:i:s') ?? 'N/A'),
            'released_date' => $record->action === 'lift' ? $record->lifted_at?->format('Y-m-d H:i:s') : null,
            'detention_duration' => $detainRecord ? $this->calculateDurationBetween($detainRecord->created_at, $record->lifted_at ?? now()) : $detentionDuration,
            'detention_duration_human' => $detainRecord ? $this->formatDuration($this->calculateDurationBetween($detainRecord->created_at, $record->lifted_at ?? now())) : $this->formatDuration($detentionDuration),
            'detained_by' => $record->action === 'detain' ? ($record->detainedBy ? [
                'id' => $record->detainedBy->id,
                'name' => $record->detainedBy->name,
            ] : null) : ($detainRecord?->detainedBy ? [
                'id' => $detainRecord->detainedBy->id,
                'name' => $detainRecord->detainedBy->name,
            ] : null),
            'lifted_by' => $record->liftedBy ? [
                'id' => $record->liftedBy->id,
                'name' => $record->liftedBy->name,
            ] : null,
            'branch' => $record->branch ? [
                'id' => $record->branch->id,
                'name' => $record->branch->name,
            ] : null,
        ];
    }

    /**
     * Get entity-specific data
     */
    private function getEntityData(DetainRecord $record): array
    {
        $data = [
            'entity_type' => class_basename($record->rtfable_type ?? ''),
            'shipment_reference' => null,
            'hbl_reference' => null,
            'package_number' => null,
        ];

        if (!$record->rtfable) {
            // If rtfable is null, try to get the reference from the record itself
            // This might happen if the entity was soft-deleted
            \Log::warning('Detain record has no rtfable', [
                'record_id' => $record->id,
                'rtfable_type' => $record->rtfable_type,
                'rtfable_id' => $record->rtfable_id,
            ]);
            
            // Try to load the entity even if soft-deleted
            if ($record->rtfable_type && $record->rtfable_id) {
                try {
                    $modelClass = $record->rtfable_type;
                    if (class_exists($modelClass)) {
                        $entity = $modelClass::withTrashed()->find($record->rtfable_id);
                        if ($entity) {
                            $record->setRelation('rtfable', $entity);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error loading soft-deleted entity: ' . $e->getMessage());
                }
            }
            
            if (!$record->rtfable) {
                return $data;
            }
        }

        try {
            switch ($record->rtfable_type) {
                case 'App\Models\Container':
                    $data['shipment_reference'] = $record->rtfable->reference ?? null;
                    break;

                case 'App\Models\HBL':
                    $hbl = $record->rtfable;
                    $data['hbl_reference'] = $hbl->reference ?? $hbl->hbl_number ?? null;
                    
                    // Try to get container reference (many-to-many relationship)
                    if ($hbl->relationLoaded('containers') && $hbl->containers->isNotEmpty()) {
                        $data['shipment_reference'] = $hbl->containers->first()->reference ?? null;
                    } elseif (!$hbl->relationLoaded('containers')) {
                        // Try to load containers if not already loaded
                        try {
                            $containers = $hbl->containers()->limit(1)->get();
                            if ($containers->isNotEmpty()) {
                                $data['shipment_reference'] = $containers->first()->reference ?? null;
                            }
                        } catch (\Exception $e) {
                            \Log::error('Error loading HBL containers: ' . $e->getMessage());
                        }
                    }
                    break;

                case 'App\Models\HBLPackage':
                    $package = $record->rtfable;
                    $data['package_number'] = $package->package_number ?? 'PKG-' . ($package->id ?? 'N/A');
                    
                    // Try to get HBL reference
                    if ($package->relationLoaded('hbl') && $package->hbl) {
                        $hbl = $package->hbl;
                        $data['hbl_reference'] = $hbl->reference ?? $hbl->hbl_number ?? null;
                        
                        // Try to get container reference (many-to-many relationship)
                        if ($hbl->relationLoaded('containers') && $hbl->containers->isNotEmpty()) {
                            $data['shipment_reference'] = $hbl->containers->first()->reference ?? null;
                        }
                    } elseif (!$package->relationLoaded('hbl')) {
                        // Try to load HBL if not already loaded
                        try {
                            $hbl = $package->hbl()->first();
                            if ($hbl) {
                                $data['hbl_reference'] = $hbl->reference ?? $hbl->hbl_number ?? null;
                                
                                $containers = $hbl->containers()->limit(1)->get();
                                if ($containers->isNotEmpty()) {
                                    $data['shipment_reference'] = $containers->first()->reference ?? null;
                                }
                            }
                        } catch (\Exception $e) {
                            \Log::error('Error loading HBLPackage relationships: ' . $e->getMessage());
                        }
                    }
                    
                    // Also try direct containers relationship on package
                    if (!$data['shipment_reference']) {
                        if ($package->relationLoaded('containers') && $package->containers->isNotEmpty()) {
                            $data['shipment_reference'] = $package->containers->first()->reference ?? null;
                        } elseif (!$package->relationLoaded('containers')) {
                            try {
                                $containers = $package->containers()->limit(1)->get();
                                if ($containers->isNotEmpty()) {
                                    $data['shipment_reference'] = $containers->first()->reference ?? null;
                                }
                            } catch (\Exception $e) {
                                \Log::error('Error loading Package containers: ' . $e->getMessage());
                            }
                        }
                    }
                    break;
            }
        } catch (\Exception $e) {
            \Log::error('Error getting entity data for detain record: ' . $e->getMessage(), [
                'record_id' => $record->id,
                'rtfable_type' => $record->rtfable_type,
                'rtfable_id' => $record->rtfable_id,
            ]);
        }

        return $data;
    }

    /**
     * Calculate detention duration in minutes
     */
    private function calculateDuration(DetainRecord $record): ?int
    {
        if ($record->action !== 'detain') {
            return null;
        }

        $startDate = $record->created_at;
        $endDate = $record->lifted_at ?? now();

        return $startDate->diffInMinutes($endDate);
    }

    /**
     * Calculate duration between two dates in minutes
     */
    private function calculateDurationBetween($startDate, $endDate): ?int
    {
        if (!$startDate) {
            return null;
        }

        return $startDate->diffInMinutes($endDate);
    }

    /**
     * Format duration in human-readable format
     */
    private function formatDuration(?int $minutes): ?string
    {
        if ($minutes === null) {
            return null;
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

    /**
     * Export Detain report
     */
    public function export(Request $request)
    {
        $this->authorize('reports.detain');

        $format = $request->input('format', 'xlsx');
        
        if ($format === 'pdf') {
            return $this->exportPDF($request);
        }

        $filename = 'detain-report-' . date('Y-m-d-His');

        return Excel::download(
            new DetainReportExport($request),
            "{$filename}.{$format}"
        );
    }

    /**
     * Export as PDF
     */
    private function exportPDF(Request $request)
    {
        try {
            $query = DetainRecord::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->with(['detainedBy', 'liftedBy', 'branch']);

            $this->applyFilters($query, $request);

            $sortField = $request->input('sort_field', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            $limit = $request->input('limit', 500);
            $limit = min($limit, 500);
            
            $records = $query->limit($limit)->get();

            if ($records->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No detain records found matching the selected criteria.'
                ], 404);
            }

            // Load polymorphic relationships conditionally
            $records->load(['rtfable' => function ($query) {
                $query->withTrashed();
            }]);

            // Load nested relationships based on type
            foreach ($records as $record) {
                if (!$record->rtfable) {
                    continue;
                }
                
                if ($record->rtfable_type === 'App\Models\Container') {
                    // Container doesn't need additional relationships
                } elseif ($record->rtfable_type === 'App\Models\HBL') {
                    $record->rtfable->load('containers');
                } elseif ($record->rtfable_type === 'App\Models\HBLPackage') {
                    $record->rtfable->load(['hbl' => function ($query) {
                        $query->withTrashed();
                    }]);
                    
                    if ($record->rtfable->hbl) {
                        $record->rtfable->hbl->load('containers');
                    }
                    
                    $record->rtfable->load('containers');
                }
            }

            // Transform records for PDF
            $transformedRecords = $records->map(function ($record) {
                return $this->transformRecord($record);
            });

            $pdf = \PDF::loadView('pdf.reports.detain-report', [
                'records' => $transformedRecords,
                'filters' => $request->all(),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ]);

            $filename = 'detain-report-' . date('Y-m-d-His') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Error exporting Detain Report PDF: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
