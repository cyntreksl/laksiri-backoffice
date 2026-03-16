<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\OverLandReportExport;
use App\Models\Branch;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class OverLandReportController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('reports.over-land');

        $branches = Branch::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn($branch) => [
                'value' => $branch->id,
                'label' => $branch->name
            ])
            ->toArray();

        return Inertia::render('Reports/OverLandReport', [
            'branches' => $branches,
        ]);
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.over-land');

        try {
            // Debug: Check if there are any overland unloading issues
            $totalOverlandIssues = DB::table('unloading_issues')
                ->whereNull('deleted_at')
                ->where('type', 'Overland')
                ->count();

            $query = DB::table('unloading_issues')
                ->join('hbl_packages', 'unloading_issues.hbl_package_id', '=', 'hbl_packages.id')
                ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
                ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                ->leftJoin('duplicate_container_hbl_package as dchp', 'hbl_packages.id', '=', 'dchp.hbl_package_id')
                ->leftJoin('containers', 'dchp.container_id', '=', 'containers.id')
                ->select([
                    'hbl.hbl_number',
                    'consignees.name as consignee_name',
                    'hbl.consignee_address as address',
                    DB::raw('SUM(hbl_packages.quantity) as tot_pkg'),
                    DB::raw('GROUP_CONCAT(DISTINCT hbl_packages.package_type) as typ_pkg'),
                    DB::raw('MAX(containers.estimated_time_of_arrival) as arrival_date'),
                    DB::raw('MAX(containers.unloading_ended_at) as destuff_date'),
                    DB::raw('COUNT(unloading_issues.id) as over_qty'),
                    DB::raw('COALESCE(MAX(containers.unloading_ended_at), MAX(unloading_issues.created_at)) as sort_date')
                ])
                ->whereNull('unloading_issues.deleted_at')
                ->whereNull('hbl_packages.deleted_at')
                ->whereNull('hbl.deleted_at')
                ->where('unloading_issues.type', 'Overland')
                ->groupBy([
                    'hbl.id',
                    'hbl.hbl_number',
                    'consignees.name',
                    'hbl.consignee_address'
                ]);

            // Apply date range filters - use container unloading date or unloading issue creation date
            if ($request->filled('date_from')) {
                $query->where(function($q) use ($request) {
                    $q->whereDate('containers.unloading_ended_at', '>=', $request->date_from)
                      ->orWhere(function($subQ) use ($request) {
                          $subQ->whereNull('containers.unloading_ended_at')
                               ->whereDate('unloading_issues.created_at', '>=', $request->date_from);
                      });
                });
            }

            if ($request->filled('date_to')) {
                $query->where(function($q) use ($request) {
                    $q->whereDate('containers.unloading_ended_at', '<=', $request->date_to)
                      ->orWhere(function($subQ) use ($request) {
                          $subQ->whereNull('containers.unloading_ended_at')
                               ->whereDate('unloading_issues.created_at', '<=', $request->date_to);
                      });
                });
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('hbl.hbl_number', 'like', "%{$search}%")
                        ->orWhere('consignees.name', 'like', "%{$search}%");
                });
            }

            // Apply branch filter
            if ($request->filled('branch_id')) {
                $query->where('hbl.branch_id', $request->branch_id);
            }

            // Apply sorting
            $sortField = $request->get('sort_field', 'sort_date');
            $sortOrder = $request->get('sort_order', 'desc');

            // Map sort fields to valid aggregated columns
            $validSortFields = [
                'hbl.hbl_number' => 'hbl.hbl_number',
                'consignees.name' => 'consignee_name',
                'tot_pkg' => 'tot_pkg',
                'arrival_date' => 'arrival_date',
                'destuff_date' => 'destuff_date',
                'sort_date' => 'sort_date',
                'over_qty' => 'over_qty'
            ];

            $actualSortField = $validSortFields[$sortField] ?? 'sort_date';
            $query->orderBy($actualSortField, $sortOrder);

            // Calculate total count before pagination
            $countQuery = DB::table('unloading_issues')
                ->join('hbl_packages', 'unloading_issues.hbl_package_id', '=', 'hbl_packages.id')
                ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
                ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                ->leftJoin('duplicate_container_hbl_package as dchp', 'hbl_packages.id', '=', 'dchp.hbl_package_id')
                ->leftJoin('containers', 'dchp.container_id', '=', 'containers.id')
                ->whereNull('unloading_issues.deleted_at')
                ->whereNull('hbl_packages.deleted_at')
                ->whereNull('hbl.deleted_at')
                ->where('unloading_issues.type', 'Overland');

            // Apply the same filters to count query
            if ($request->filled('date_from')) {
                $countQuery->where(function($q) use ($request) {
                    $q->whereDate('containers.unloading_ended_at', '>=', $request->date_from)
                      ->orWhere(function($subQ) use ($request) {
                          $subQ->whereNull('containers.unloading_ended_at')
                               ->whereDate('unloading_issues.created_at', '>=', $request->date_from);
                      });
                });
            }

            if ($request->filled('date_to')) {
                $countQuery->where(function($q) use ($request) {
                    $q->whereDate('containers.unloading_ended_at', '<=', $request->date_to)
                      ->orWhere(function($subQ) use ($request) {
                          $subQ->whereNull('containers.unloading_ended_at')
                               ->whereDate('unloading_issues.created_at', '<=', $request->date_to);
                      });
                });
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $countQuery->where(function ($q) use ($search) {
                    $q->where('hbl.hbl_number', 'like', "%{$search}%")
                        ->orWhere('consignees.name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('branch_id')) {
                $countQuery->where('hbl.branch_id', $request->branch_id);
            }

            $total = $countQuery->distinct('hbl.id')->count('hbl.id');

            // Calculate stats
            $totalOverlandPackages = DB::table('unloading_issues')
                ->join('hbl_packages', 'unloading_issues.hbl_package_id', '=', 'hbl_packages.id')
                ->whereNull('unloading_issues.deleted_at')
                ->whereNull('hbl_packages.deleted_at')
                ->where('unloading_issues.type', 'Overland')
                ->count();

            $stats = [
                'total_hbls' => $total,
                'total_overland_packages' => $totalOverlandPackages,
                'debug_total_overland_in_db' => $totalOverlandIssues
            ];

            // Pagination
            $perPage = $request->get('per_page', 25);
            $page = $request->get('page', 1);

            $data = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            // Add serial numbers and format data
            $data = $data->map(function ($item, $index) use ($page, $perPage) {
                $item->serial_no = (($page - 1) * $perPage) + $index + 1;
                $item->tot_pkg = $item->tot_pkg ?: 0;
                $item->over_qty = $item->over_qty ?: 0;
                $item->arrival_date = $item->arrival_date ? date('d/m/Y', strtotime($item->arrival_date)) : '';
                $item->destuff_date = $item->destuff_date ? date('d/m/Y', strtotime($item->destuff_date)) : '';
                return $item;
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => $total,
                'stats' => $stats,
                'debug' => [
                    'total_overland_issues_in_db' => $totalOverlandIssues,
                    'filters_applied' => [
                        'date_from' => $request->get('date_from'),
                        'date_to' => $request->get('date_to'),
                        'search' => $request->get('search')
                    ]
                ]
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
        $this->authorize('reports.over-land');

        $filters = $request->only(['date_from', 'date_to', 'search', 'branch_id']);
        $format = $request->get('format', 'xlsx');

        $export = new OverLandReportExport($filters);

        $filename = 'over_land_report_' . date('Y_m_d_H_i_s') . '.' . $format;

        if ($format === 'pdf') {
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::DOMPDF);
        }

        return Excel::download($export, $filename);
    }

    public function debug(Request $request)
    {
        $this->authorize('reports.over-land');

        // Check overland unloading issues
        $totalOverlandIssues = DB::table('unloading_issues')
            ->whereNull('deleted_at')
            ->where('type', 'Overland')
            ->count();

        // Get sample overland issues
        $sampleOverlandIssues = DB::table('unloading_issues')
            ->join('hbl_packages', 'unloading_issues.hbl_package_id', '=', 'hbl_packages.id')
            ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
            ->select('unloading_issues.id', 'unloading_issues.type', 'hbl.hbl_number', 'unloading_issues.created_at')
            ->whereNull('unloading_issues.deleted_at')
            ->where('unloading_issues.type', 'Overland')
            ->limit(5)
            ->get();

        // Check different issue types
        $issueTypes = DB::table('unloading_issues')
            ->select('type', DB::raw('COUNT(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('type')
            ->get();

        // Check HBLs with overland issues
        $hblsWithOverlandIssues = DB::table('unloading_issues')
            ->join('hbl_packages', 'unloading_issues.hbl_package_id', '=', 'hbl_packages.id')
            ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
            ->whereNull('unloading_issues.deleted_at')
            ->whereNull('hbl_packages.deleted_at')
            ->whereNull('hbl.deleted_at')
            ->where('unloading_issues.type', 'Overland')
            ->distinct('hbl.id')
            ->count('hbl.id');

        return response()->json([
            'debug_info' => [
                'total_overland_issues' => $totalOverlandIssues,
                'hbls_with_overland_issues' => $hblsWithOverlandIssues,
                'sample_overland_issues' => $sampleOverlandIssues,
                'all_issue_types' => $issueTypes,
            ]
        ]);
    }
}
