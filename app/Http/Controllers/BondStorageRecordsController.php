<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\BondStorageRecordsExport;
use App\Models\Branch;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class BondStorageRecordsController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('reports.bond-storage-records');
        
        $branches = Branch::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn($branch) => [
                'value' => $branch->id,
                'label' => $branch->name
            ])
            ->toArray();
        
        return Inertia::render('Reports/BondStorageRecords', [
            'branches' => $branches,
        ]);
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.bond-storage-records');
        
        try {
            $query = DB::table('hbl_packages')
                ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
                ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                ->join('branches', 'hbl.branch_id', '=', 'branches.id')
                ->leftJoin('container_hbl_package', 'hbl_packages.id', '=', 'container_hbl_package.hbl_package_id')
                ->leftJoin('containers', 'container_hbl_package.container_id', '=', 'containers.id')
                ->leftJoin('detain_records', function($join) {
                    $join->on('hbl_packages.id', '=', 'detain_records.rtfable_id')
                         ->where('detain_records.rtfable_type', '=', 'App\\Models\\HBLPackage');
                })
                ->select([
                    'hbl.hbl_number',
                    'consignees.name as consignee_name',
                    'hbl_packages.quantity as pkgs',
                    'hbl_packages.bond_storage_number as bs_no',
                    'detain_records.note as remarks',
                    'branches.name as agent_name',
                    'containers.reference as container_reference',
                    'containers.vessel_name',
                    'containers.unloading_ended_at as destuffing_date',
                    'hbl.reference as hbl_reference',
                    'hbl_packages.created_at'
                ])
                ->whereNotNull('hbl_packages.bond_storage_number')
                ->whereNull('hbl.deleted_at')
                ->whereNull('hbl_packages.deleted_at');

            // Apply date range filters
            if ($request->filled('date_from')) {
                $query->whereDate('hbl_packages.created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('hbl_packages.created_at', '<=', $request->date_to);
            }

            // Apply agent filter
            if ($request->filled('agent_id')) {
                $query->where('hbl.branch_id', $request->agent_id);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('hbl.hbl_number', 'like', "%{$search}%")
                        ->orWhere('hbl_packages.bond_storage_number', 'like', "%{$search}%")
                        ->orWhere('consignees.name', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortField = $request->get('sort_field', 'hbl_packages.created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            // Calculate total count before pagination
            $countQuery = clone $query;
            $total = $countQuery->count();

            // Calculate stats using separate queries
            $statsBaseQuery = DB::table('hbl_packages')
                ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
                ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                ->join('branches', 'hbl.branch_id', '=', 'branches.id')
                ->whereNotNull('hbl_packages.bond_storage_number')
                ->whereNull('hbl.deleted_at')
                ->whereNull('hbl_packages.deleted_at');

            // Apply the same filters to stats query
            if ($request->filled('date_from')) {
                $statsBaseQuery->whereDate('hbl_packages.created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $statsBaseQuery->whereDate('hbl_packages.created_at', '<=', $request->date_to);
            }
            if ($request->filled('agent_id')) {
                $statsBaseQuery->where('hbl.branch_id', $request->agent_id);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $statsBaseQuery->where(function ($q) use ($search) {
                    $q->where('hbl.hbl_number', 'like', "%{$search}%")
                        ->orWhere('hbl_packages.bond_storage_number', 'like', "%{$search}%")
                        ->orWhere('consignees.name', 'like', "%{$search}%");
                });
            }

            $stats = [
                'total_packages' => $total,
                'total_consignees' => (clone $statsBaseQuery)->distinct('hbl.consignee_id')->count('hbl.consignee_id'),
                'total_package_count' => (clone $statsBaseQuery)->sum('hbl_packages.quantity'),
            ];

            // Pagination
            $perPage = $request->get('per_page', 25);
            $page = $request->get('page', 1);
            
            $data = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => $total,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $this->authorize('reports.bond-storage-records');
        
        $filters = $request->only(['date_from', 'date_to', 'agent_id', 'search']);
        $format = $request->get('format', 'xlsx');

        $export = new BondStorageRecordsExport($filters);

        $filename = 'bond_storage_records_' . date('Y_m_d_H_i_s') . '.' . $format;

        if ($format === 'pdf') {
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::DOMPDF);
        }

        return Excel::download($export, $filename);
    }
}