<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\AgeAnalysisConsigneeExport;
use App\Models\Branch;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class AgeAnalysisConsigneeController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('reports.age-analysis-consignee');

        $branches = Branch::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn($branch) => [
                'value' => $branch->id,
                'label' => $branch->name
            ])
            ->toArray();

        return Inertia::render('Reports/AgeAnalysisConsignee', [
            'branches' => $branches,
        ]);
    }

    public function getContainers(Request $request)
    {
        $this->authorize('reports.age-analysis-consignee');

        $query = DB::table('containers')
            ->select('containers.id', 'containers.container_number', 'containers.reference')
            ->whereNotNull('containers.container_number')
            ->orderBy('containers.container_number');

        // Filter by agent from and to if provided
        if ($request->filled('agent_from') || $request->filled('agent_to')) {
            $query->join('duplicate_container_hbl_package as dchp', 'containers.id', '=', 'dchp.container_id')
                ->join('hbl_packages', 'dchp.hbl_package_id', '=', 'hbl_packages.id')
                ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id');

            if ($request->filled('agent_from')) {
                $query->where('hbl.branch_id', $request->agent_from);
            }

            if ($request->filled('agent_to')) {
                $query->where('hbl.warehouse_id', $request->agent_to);
            }

            $query->distinct();
        }

        $containers = $query->get()->map(fn($container) => [
            'value' => $container->id,
            'label' => $container->container_number . ($container->reference ? ' (' . $container->reference . ')' : '')
        ])->toArray();

        return response()->json($containers);
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.age-analysis-consignee');
        
        try {
            // Temporary debug for specific container
            if ($request->filled('container_id')) {
                $containerInfo = DB::table('containers')
                    ->where('id', (int)$request->container_id)
                    ->first();
                    
                $containerHBLCount = DB::table('containers')
                    ->join('duplicate_container_hbl_package as dchp', 'containers.id', '=', 'dchp.container_id')
                    ->join('hbl_packages', 'dchp.hbl_package_id', '=', 'hbl_packages.id')
                    ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
                    ->where('containers.id', (int)$request->container_id)
                    ->whereNull('hbl.deleted_at')
                    ->count();
                    
                $debugInfo = [
                    'container_id' => $request->container_id,
                    'container_info' => $containerInfo,
                    'hbl_count_for_container' => $containerHBLCount,
                ];
            }

            // Check if container filter is applied - use different query structure
            if ($request->filled('container_id')) {
                // When filtering by container, use INNER JOINs to get only HBLs linked to that container
                $query = DB::table('hbl')
                    ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                    ->join('branches as agent_from', 'hbl.branch_id', '=', 'agent_from.id')
                    ->leftJoin('branches as agent_to', 'hbl.warehouse_id', '=', 'agent_to.id')
                    ->join('hbl_packages', 'hbl.id', '=', 'hbl_packages.hbl_id')
                    ->join('duplicate_container_hbl_package as dchp', 'hbl_packages.id', '=', 'dchp.hbl_package_id')
                    ->join('containers', 'dchp.container_id', '=', 'containers.id')
                    ->select([
                        'hbl.hbl_number',
                        'consignees.name as consignee_name',
                        'hbl.consignee_address as address',
                        'agent_from.name as agent_from_name',
                        'agent_to.name as agent_to_name',
                        'containers.container_number',
                        'containers.reference as cargo_manifest_no',
                        DB::raw('COALESCE(containers.unloading_ended_at, hbl.created_at) as destuffing_date'),
                        DB::raw('COALESCE(DATEDIFF(CURDATE(), containers.unloading_ended_at), DATEDIFF(CURDATE(), hbl.created_at)) as no_of_days'),
                        DB::raw('SUM(hbl_packages.quantity) as qty_manifest'),
                        DB::raw('SUM(hbl_packages.quantity) as qty_actual'),
                        DB::raw('GROUP_CONCAT(DISTINCT hbl_packages.package_type) as type_of_package'),
                        'hbl.created_at'
                    ])
                    ->whereNull('hbl.deleted_at')
                    ->where('containers.id', (int)$request->container_id)
                    ->groupBy([
                        'hbl.id',
                        'hbl.hbl_number',
                        'consignees.name',
                        'hbl.consignee_address',
                        'agent_from.name',
                        'agent_to.name',
                        'containers.container_number',
                        'containers.reference',
                        'containers.unloading_ended_at',
                        'hbl.created_at'
                    ]);
            } else {
                // When no container filter, use simple HBL data
                $query = DB::table('hbl')
                    ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                    ->join('branches as agent_from', 'hbl.branch_id', '=', 'agent_from.id')
                    ->leftJoin('branches as agent_to', 'hbl.warehouse_id', '=', 'agent_to.id')
                    ->select([
                        'hbl.hbl_number',
                        'consignees.name as consignee_name',
                        'hbl.consignee_address as address',
                        'agent_from.name as agent_from_name',
                        'agent_to.name as agent_to_name',
                        DB::raw("'' as container_number"),
                        DB::raw("'' as cargo_manifest_no"),
                        DB::raw('hbl.created_at as destuffing_date'),
                        DB::raw('DATEDIFF(CURDATE(), hbl.created_at) as no_of_days'),
                        DB::raw('0 as qty_manifest'),
                        DB::raw('0 as qty_actual'),
                        DB::raw("'' as type_of_package"),
                        'hbl.created_at'
                    ])
                    ->whereNull('hbl.deleted_at');
            }

            // Apply agent filters
            if ($request->filled('agent_from')) {
                $query->where('hbl.branch_id', (int)$request->agent_from);
            }
            if ($request->filled('agent_to')) {
                $query->where('hbl.warehouse_id', (int)$request->agent_to);
            }

            // Apply date filters
            if ($request->filled('date_from')) {
                if ($request->filled('container_id')) {
                    // When filtering by container, use container unloading date or HBL creation date
                    $query->where(function($q) use ($request) {
                        $q->whereDate('containers.unloading_ended_at', '>=', $request->date_from)
                          ->orWhere(function($subQ) use ($request) {
                              $subQ->whereNull('containers.unloading_ended_at')
                                   ->whereDate('hbl.created_at', '>=', $request->date_from);
                          });
                    });
                } else {
                    $query->whereDate('hbl.created_at', '>=', $request->date_from);
                }
            }
            if ($request->filled('date_to')) {
                if ($request->filled('container_id')) {
                    // When filtering by container, use container unloading date or HBL creation date
                    $query->where(function($q) use ($request) {
                        $q->whereDate('containers.unloading_ended_at', '<=', $request->date_to)
                          ->orWhere(function($subQ) use ($request) {
                              $subQ->whereNull('containers.unloading_ended_at')
                                   ->whereDate('hbl.created_at', '<=', $request->date_to);
                          });
                    });
                } else {
                    $query->whereDate('hbl.created_at', '<=', $request->date_to);
                }
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('hbl.hbl_number', 'like', "%{$search}%")
                        ->orWhere('consignees.name', 'like', "%{$search}%");
                    
                    // Add container reference search if container data is available
                    if ($request->filled('container_id')) {
                        $q->orWhere('containers.reference', 'like', "%{$search}%");
                    }
                });
            }

            // Apply sorting
            $sortField = $request->get('sort_field', 'destuffing_date');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if ($sortField === 'destuffing_date') {
                if ($request->filled('container_id')) {
                    $query->orderBy(DB::raw('COALESCE(containers.unloading_ended_at, hbl.created_at)'), $sortOrder);
                } else {
                    $query->orderBy('hbl.created_at', $sortOrder);
                }
            } elseif ($sortField === 'hbl.hbl_number') {
                $query->orderBy('hbl.hbl_number', $sortOrder);
            } elseif ($sortField === 'consignee_name') {
                $query->orderBy('consignees.name', $sortOrder);
            } elseif ($sortField === 'qty_manifest' && $request->filled('container_id')) {
                $query->orderBy(DB::raw('SUM(hbl_packages.quantity)'), $sortOrder);
            } else {
                if ($request->filled('container_id')) {
                    $query->orderBy(DB::raw('COALESCE(containers.unloading_ended_at, hbl.created_at)'), $sortOrder);
                } else {
                    $query->orderBy('hbl.created_at', $sortOrder);
                }
            }

            // Calculate total count
            if ($request->filled('container_id')) {
                // For grouped queries, we need to count distinct HBLs
                $total = DB::table('hbl')
                    ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                    ->join('branches as agent_from', 'hbl.branch_id', '=', 'agent_from.id')
                    ->leftJoin('branches as agent_to', 'hbl.warehouse_id', '=', 'agent_to.id')
                    ->join('hbl_packages', 'hbl.id', '=', 'hbl_packages.hbl_id')
                    ->join('duplicate_container_hbl_package as dchp', 'hbl_packages.id', '=', 'dchp.hbl_package_id')
                    ->join('containers', 'dchp.container_id', '=', 'containers.id')
                    ->whereNull('hbl.deleted_at')
                    ->where('containers.id', (int)$request->container_id);
                    
                // Apply the same filters as the main query
                if ($request->filled('agent_from')) {
                    $total->where('hbl.branch_id', (int)$request->agent_from);
                }
                if ($request->filled('agent_to')) {
                    $total->where('hbl.warehouse_id', (int)$request->agent_to);
                }
                if ($request->filled('date_from')) {
                    $total->where(function($q) use ($request) {
                        $q->whereDate('containers.unloading_ended_at', '>=', $request->date_from)
                          ->orWhere(function($subQ) use ($request) {
                              $subQ->whereNull('containers.unloading_ended_at')
                                   ->whereDate('hbl.created_at', '>=', $request->date_from);
                          });
                    });
                }
                if ($request->filled('date_to')) {
                    $total->where(function($q) use ($request) {
                        $q->whereDate('containers.unloading_ended_at', '<=', $request->date_to)
                          ->orWhere(function($subQ) use ($request) {
                              $subQ->whereNull('containers.unloading_ended_at')
                                   ->whereDate('hbl.created_at', '<=', $request->date_to);
                          });
                    });
                }
                if ($request->filled('search')) {
                    $search = $request->search;
                    $total->where(function ($q) use ($search) {
                        $q->where('hbl.hbl_number', 'like', "%{$search}%")
                            ->orWhere('consignees.name', 'like', "%{$search}%")
                            ->orWhere('containers.reference', 'like', "%{$search}%");
                    });
                }
                
                $total = $total->distinct('hbl.id')->count('hbl.id');
            } else {
                $total = $query->count();
            }

            // Calculate stats
            if ($request->filled('container_id')) {
                // Create a separate query for package count without GROUP BY
                $packageQuery = DB::table('hbl')
                    ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                    ->join('branches as agent_from', 'hbl.branch_id', '=', 'agent_from.id')
                    ->leftJoin('branches as agent_to', 'hbl.warehouse_id', '=', 'agent_to.id')
                    ->join('hbl_packages', 'hbl.id', '=', 'hbl_packages.hbl_id')
                    ->join('duplicate_container_hbl_package as dchp', 'hbl_packages.id', '=', 'dchp.hbl_package_id')
                    ->join('containers', 'dchp.container_id', '=', 'containers.id')
                    ->whereNull('hbl.deleted_at')
                    ->where('containers.id', (int)$request->container_id);
                    
                // Apply the same filters as the main query
                if ($request->filled('agent_from')) {
                    $packageQuery->where('hbl.branch_id', (int)$request->agent_from);
                }
                if ($request->filled('agent_to')) {
                    $packageQuery->where('hbl.warehouse_id', (int)$request->agent_to);
                }
                if ($request->filled('date_from')) {
                    $packageQuery->where(function($q) use ($request) {
                        $q->whereDate('containers.unloading_ended_at', '>=', $request->date_from)
                          ->orWhere(function($subQ) use ($request) {
                              $subQ->whereNull('containers.unloading_ended_at')
                                   ->whereDate('hbl.created_at', '>=', $request->date_from);
                          });
                    });
                }
                if ($request->filled('date_to')) {
                    $packageQuery->where(function($q) use ($request) {
                        $q->whereDate('containers.unloading_ended_at', '<=', $request->date_to)
                          ->orWhere(function($subQ) use ($request) {
                              $subQ->whereNull('containers.unloading_ended_at')
                                   ->whereDate('hbl.created_at', '<=', $request->date_to);
                          });
                    });
                }
                if ($request->filled('search')) {
                    $search = $request->search;
                    $packageQuery->where(function ($q) use ($search) {
                        $q->where('hbl.hbl_number', 'like', "%{$search}%")
                            ->orWhere('consignees.name', 'like', "%{$search}%")
                            ->orWhere('containers.reference', 'like', "%{$search}%");
                    });
                }
                
                $packageTotal = $packageQuery->sum('hbl_packages.quantity') ?: 0;
            } else {
                $packageTotal = 0;
            }

            $stats = [
                'total_hbls' => $total,
                'total_packages' => $packageTotal,
            ];

            // Pagination
            $perPage = $request->get('per_page', 25);
            $page = $request->get('page', 1);
            
            $data = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            // Add serial numbers and format data
            $data = $data->map(function ($item, $index) use ($page, $perPage) {
                // Remove serial_no assignment
                $item->qty_manifest = $item->qty_manifest ?: 0;
                $item->qty_actual = $item->qty_actual ?: 0;
                $item->no_of_days = $item->no_of_days ?: 0;
                $item->destuffing_date = $item->destuffing_date ? date('d/m/Y', strtotime($item->destuffing_date)) : '';
                return $item;
            });

            $response = [
                'success' => true,
                'data' => $data,
                'total' => $total,
                'stats' => $stats,
            ];
            
            // Add debug info if container filter is applied
            if ($request->filled('container_id')) {
                $response['debug'] = $debugInfo ?? [];
            }
            
            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $this->authorize('reports.age-analysis-consignee');
        
        $filters = $request->only(['date_from', 'date_to', 'agent_from', 'agent_to', 'container_id', 'search']);
        $format = $request->get('format', 'xlsx');

        $export = new AgeAnalysisConsigneeExport($filters);

        $filename = 'age_analysis_consignee_' . date('Y_m_d_H_i_s') . '.' . $format;

        if ($format === 'pdf') {
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::DOMPDF);
        }

        return Excel::download($export, $filename);
    }
}