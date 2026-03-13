<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ShortLandReportExport;
use App\Models\Branch;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class ShortLandReportController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('reports.short-land');
        
        $branches = Branch::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn($branch) => [
                'value' => $branch->id,
                'label' => $branch->name
            ])
            ->toArray();
        
        return Inertia::render('Reports/ShortLandReport', [
            'branches' => $branches,
        ]);
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.short-land');
        
        try {
            $query = DB::table('hbl')
                ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                ->join('branches', 'hbl.branch_id', '=', 'branches.id')
                ->leftJoin('hbl_packages', 'hbl.id', '=', 'hbl_packages.hbl_id')
                ->leftJoin('container_hbl_package', 'hbl_packages.id', '=', 'container_hbl_package.hbl_package_id')
                ->leftJoin('containers', 'container_hbl_package.container_id', '=', 'containers.id')
                ->select([
                    'hbl.hbl_number',
                    'hbl.reference',
                    'consignees.name as consignee_name',
                    'hbl.consignee_address as address',
                    'branches.name as agent_name',
                    DB::raw('MAX(containers.estimated_time_of_arrival) as arrival_date'),
                    DB::raw('MAX(containers.unloading_ended_at) as destuff_date'),
                    DB::raw('COUNT(hbl_packages.id) as main_qty'),
                    DB::raw('GROUP_CONCAT(DISTINCT hbl_packages.package_type) as typ_pkg'),
                    DB::raw('SUM(CASE WHEN hbl_packages.is_shortland = 1 THEN 1 ELSE 0 END) as short_qty'),
                    DB::raw('SUM(CASE WHEN hbl_packages.is_shortland = 0 OR hbl_packages.is_shortland IS NULL THEN 1 ELSE 0 END) as reci_qty')
                ])
                ->whereNull('hbl.deleted_at')
                ->whereNull('hbl_packages.deleted_at')
                ->where('hbl.is_shortland', true)
                ->groupBy([
                    'hbl.id',
                    'hbl.hbl_number', 
                    'hbl.reference',
                    'consignees.name',
                    'hbl.consignee_address',
                    'branches.name'
                ]);

            // Apply date range filters
            if ($request->filled('date_from')) {
                $query->whereDate('hbl.created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('hbl.created_at', '<=', $request->date_to);
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
                        ->orWhere('hbl.reference', 'like', "%{$search}%")
                        ->orWhere('consignees.name', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortField = $request->get('sort_field', 'hbl.created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            // Calculate total count before pagination (count distinct HBLs)
            $countQuery = DB::table('hbl')
                ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                ->join('branches', 'hbl.branch_id', '=', 'branches.id')
                ->leftJoin('hbl_packages', 'hbl.id', '=', 'hbl_packages.hbl_id')
                ->leftJoin('container_hbl_package', 'hbl_packages.id', '=', 'container_hbl_package.hbl_package_id')
                ->leftJoin('containers', 'container_hbl_package.container_id', '=', 'containers.id')
                ->whereNull('hbl.deleted_at')
                ->whereNull('hbl_packages.deleted_at')
                ->where('hbl.is_shortland', true);

            // Apply the same filters to count query
            if ($request->filled('date_from')) {
                $countQuery->whereDate('hbl.created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $countQuery->whereDate('hbl.created_at', '<=', $request->date_to);
            }

            if ($request->filled('agent_id')) {
                $countQuery->where('hbl.branch_id', $request->agent_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $countQuery->where(function ($q) use ($search) {
                    $q->where('hbl.hbl_number', 'like', "%{$search}%")
                        ->orWhere('hbl.reference', 'like', "%{$search}%")
                        ->orWhere('consignees.name', 'like', "%{$search}%");
                });
            }

            $total = $countQuery->distinct('hbl.id')->count('hbl.id');

            // Calculate stats using a separate query with same filters
            $statsQuery = DB::table('hbl')
                ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                ->join('branches', 'hbl.branch_id', '=', 'branches.id')
                ->leftJoin('hbl_packages', 'hbl.id', '=', 'hbl_packages.hbl_id')
                ->leftJoin('container_hbl_package', 'hbl_packages.id', '=', 'container_hbl_package.hbl_package_id')
                ->leftJoin('containers', 'container_hbl_package.container_id', '=', 'containers.id')
                ->whereNull('hbl.deleted_at')
                ->whereNull('hbl_packages.deleted_at')
                ->where('hbl.is_shortland', true);

            // Apply the same filters to stats query
            if ($request->filled('date_from')) {
                $statsQuery->whereDate('hbl.created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $statsQuery->whereDate('hbl.created_at', '<=', $request->date_to);
            }

            if ($request->filled('agent_id')) {
                $statsQuery->where('hbl.branch_id', $request->agent_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $statsQuery->where(function ($q) use ($search) {
                    $q->where('hbl.hbl_number', 'like', "%{$search}%")
                        ->orWhere('hbl.reference', 'like', "%{$search}%")
                        ->orWhere('consignees.name', 'like', "%{$search}%");
                });
            }

            $stats = [
                'total_hbls' => $total,
                'total_short_packages' => $statsQuery->where('hbl_packages.is_shortland', true)->count(),
            ];

            // Pagination
            $perPage = $request->get('per_page', 25);
            $page = $request->get('page', 1);
            
            $data = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            // Add serial numbers
            $data = $data->map(function ($item, $index) use ($page, $perPage) {
                $item->serial_no = (($page - 1) * $perPage) + $index + 1;
                return $item;
            });

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
        $this->authorize('reports.short-land');
        
        $filters = $request->only(['date_from', 'date_to', 'agent_id', 'search']);
        $format = $request->get('format', 'xlsx');

        $export = new ShortLandReportExport($filters);

        $filename = 'short_land_report_' . date('Y_m_d_H_i_s') . '.' . $format;

        if ($format === 'pdf') {
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::DOMPDF);
        }

        return Excel::download($export, $filename);
    }
}