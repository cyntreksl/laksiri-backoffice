<?php

namespace App\Http\Controllers;

use App\Models\DetainRecord;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLPackage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

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
            ->with(['detainedBy', 'liftedBy', 'rtfable', 'branch']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Get total count before pagination
        $totalRecords = $query->count();

        // Apply sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Apply pagination
        $perPage = $request->input('per_page', 25);
        $page = $request->input('page', 1);
        $records = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        // Transform data
        $data = $records->map(function ($record) {
            return $this->transformRecord($record);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $totalRecords,
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

        return [
            'id' => $record->id,
            'shipment_reference' => $entityData['shipment_reference'],
            'hbl_reference' => $entityData['hbl_reference'],
            'package_number' => $entityData['package_number'],
            'entity_level' => $record->entity_level,
            'entity_type' => $entityData['entity_type'],
            'detain_type' => $record->detain_type,
            'detain_reason' => $record->detain_reason,
            'lift_reason' => $record->lift_reason,
            'remarks' => $record->remarks,
            'action' => $record->action,
            'status' => $record->action === 'detain' ? 'Detained' : 'Released',
            'detained_date' => $record->created_at?->format('Y-m-d H:i:s'),
            'released_date' => $record->lifted_at?->format('Y-m-d H:i:s'),
            'detention_duration' => $detentionDuration,
            'detention_duration_human' => $this->formatDuration($detentionDuration),
            'detained_by' => $record->detainedBy ? [
                'id' => $record->detainedBy->id,
                'name' => $record->detainedBy->name,
            ] : null,
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
            return $data;
        }

        switch ($record->rtfable_type) {
            case 'App\Models\Container':
                $data['shipment_reference'] = $record->rtfable->reference;
                break;

            case 'App\Models\HBL':
                $hbl = $record->rtfable;
                $data['hbl_reference'] = $hbl->reference;
                if ($hbl->container) {
                    $data['shipment_reference'] = $hbl->container->reference;
                }
                break;

            case 'App\Models\HBLPackage':
                $package = $record->rtfable;
                $data['package_number'] = $package->package_number;
                if ($package->hbl) {
                    $data['hbl_reference'] = $package->hbl->reference;
                    if ($package->hbl->container) {
                        $data['shipment_reference'] = $package->hbl->container->reference;
                    }
                }
                break;
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
}
