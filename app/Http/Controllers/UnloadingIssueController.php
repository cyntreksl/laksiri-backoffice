<?php

namespace App\Http\Controllers;

use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\UnloadingIssuesRepositoryInterface;
use App\Models\Container;
use App\Models\HBL;
use App\Models\UnloadingIssue;
use App\Models\UnloadingIssueFile;
use App\Services\ShortlandHandlingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnloadingIssueController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly UnloadingIssuesRepositoryInterface $unloadingIssuesRepository,
        private readonly ContainerRepositoryInterface $ContainerRepository,
        private readonly ShortlandHandlingService $shortlandService,
    ) {}

    public function index()
    {
        $this->authorize('issues.index');

        return Inertia::render('Arrival/UnloadingIssueList');
    }

    public function create()
    {
        // Get containers for selection
        $containers = Container::select('id', 'container_number')
            ->whereNotNull('container_number')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Arrival/CreateUnloadingIssue', [
            'containers' => $containers,
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => route('dashboard')],
                ['title' => 'Unloading Issues', 'url' => route('arrival.unloading-issues.index')],
                ['title' => 'Create', 'current' => true],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('issues.index');

        $validated = $request->validate([
            'container_id' => 'required|exists:containers,id',
            'issue_type' => 'required|in:Unmanifest,Overland,Shortland,Damage,Other',
            'selected_packages' => 'required|array|min:1',
            'selected_packages.*' => 'exists:hbl_packages,id',
            'create_another' => 'nullable|boolean',
            'remarks' => 'required_if:issue_type,Damage,Other|nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        try {
            // Check for existing issues
            $packagesWithIssues = UnloadingIssue::whereIn('hbl_package_id', $validated['selected_packages'])
                ->pluck('hbl_package_id')
                ->toArray();

            if (!empty($packagesWithIssues)) {
                $errorMessage = 'Some packages already have unloading issues and cannot be processed again.';

                if ($request->boolean('create_another')) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 422);
                }

                return back()->withErrors(['selected_packages' => $errorMessage]);
            }

            // Create issues for packages without existing issues
            foreach ($validated['selected_packages'] as $packageId) {
                $issue = UnloadingIssue::create([
                    'hbl_package_id' => $packageId,
                    'issue' => $validated['issue_type'],
                    'type' => $validated['issue_type'],
                    'is_damaged' => in_array($validated['issue_type'], ['Damage']),
                    'rtf' => false,
                    'is_fixed' => false,
                    'remarks' => $validated['remarks'] ?? null,
                ]);

                // Handle photo uploads if provided
                if ($request->hasFile('photos')) {
                    foreach ($request->file('photos') as $photo) {
                        $unloadingIssueFile = new UnloadingIssueFile();
                        $unloadingIssueFile->package_id = $packageId;
                        $unloadingIssueFile->name = $photo->getClientOriginalName();
                        $unloadingIssueFile->save();
                        $unloadingIssueFile->addMedia($photo)->toMediaCollection();
                    }
                }
            }

            // Auto-mark Shortland at HBL level if issue type is Shortland
            if ($validated['issue_type'] === 'Shortland') {
                $this->shortlandService->markAsShortland(
                    $validated['selected_packages'],
                    auth()->id()
                );
            }

            // If create_another is true, return JSON response (no redirect)
            if ($request->boolean('create_another')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Unloading issues created successfully!'
                ]);
            }

            // Otherwise redirect to index
            return redirect()->route('arrival.unloading-issues.index')
                ->with('success', 'Unloading issues created successfully!');
        } catch (\Exception $e) {
            if ($request->boolean('create_another')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create unloading issues: ' . $e->getMessage()
                ], 422);
            }
            return back()->withErrors(['error' => 'Failed to create unloading issues: ' . $e->getMessage()]);
        }
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate']);

        return $this->unloadingIssuesRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function getUnloadingIssuesByHbl(HBL $hbl)
    {
        return $this->unloadingIssuesRepository->getUnloadingIssuesByHbl($hbl);
    }

    public function getUnloadingIssuesImage(unloadingIssue $unloadingIssue)
    {
        return $this->ContainerRepository->downloadUnloadingIssueImages($unloadingIssue);
    }

    public function destroyUnloadingIssueImage(UnloadingIssueFile $unloadingIssueFile)
    {
        return $this->ContainerRepository->deleteUnloadingIssueFile($unloadingIssueFile);
    }

    public function downloadUnloadingIssueFile($id)
    {
        return $this->ContainerRepository->downloadSingleUnloadingIssueFile($id);
    }

    public function destroy(UnloadingIssue $unloadingIssue)
    {
        $this->authorize('issues.index');

        try {
            $unloadingIssue->delete();

            return response()->json([
                'success' => true,
                'message' => 'Unloading issue deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete unloading issue: ' . $e->getMessage()
            ], 422);
        }
    }

    public function searchHBLPackages(Request $request)
    {
        $hblNumber = $request->input('hbl_number');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $containerId = $request->input('container_id');

        if (!$hblNumber || strlen($hblNumber) < 3) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'last_page' => 1,
                ]
            ]);
        }

        $query = \App\Models\HBLPackage::query()
            ->with(['hbl' => function($q) {
                $q->withoutGlobalScopes()
                  ->select('id', 'hbl_number', 'hbl_name', 'system_status')
                  ->where('system_status', '!=', 6.8)
                  ->with(['mhbls' => function($mhblQ) {
                      $mhblQ->withoutGlobalScopes()->select('mhbls.id', 'mhbls.hbl_number');
                  }]);
            }, 'unloadingIssue'])
            ->whereHas('hbl', function ($q) use ($hblNumber) {
                $q->withoutGlobalScopes()
                  ->where('system_status', '!=', 6.8)
                  ->where(function($subQ) use ($hblNumber) {
                      // Search by HBL number
                      $subQ->where('hbl_number', 'like', "%{$hblNumber}%")
                           // Also search by MHBL number
                           ->orWhereHas('mhbls', function($mhblQ) use ($hblNumber) {
                               $mhblQ->withoutGlobalScopes()
                                     ->where('hbl_number', 'like', "%{$hblNumber}%");
                           });
                  });
            });

//        if ($containerId) {
//            $query->whereHas('containers', function ($cq) use ($containerId) {
//                $cq->where('container_id', $containerId);
//            });
//        }

        $packages = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $packages->map(function ($package) use ($hblNumber) {
            $existingIssue = $package->unloadingIssue->first();
            
            // Check if this is an MHBL match
            $isMhblMatch = false;
            $mhblNumber = null;
            
            if ($package->hbl && $package->hbl->mhbls) {
                foreach ($package->hbl->mhbls as $mhbl) {
                    if (stripos($mhbl->hbl_number, $hblNumber) !== false) {
                        $isMhblMatch = true;
                        $mhblNumber = $mhbl->hbl_number;
                        break;
                    }
                }
            }

            return [
                'id' => $package->id,
                'hbl_number' => $package->hbl->hbl_number ?? '',
                'hbl_name' => $package->hbl->hbl_name ?? '',
                'package_number' => $package->package_number,
                'weight' => $package->weight,
                'volume' => $package->volume,
                'has_unloading_issue' => $package->unloadingIssue->isNotEmpty(),
                'existing_issue_type' => $existingIssue ? $existingIssue->type : null,
                'is_mhbl_match' => $isMhblMatch,
                'mhbl_number' => $mhblNumber,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $packages->total(),
                'current_page' => $packages->currentPage(),
                'per_page' => $packages->perPage(),
                'last_page' => $packages->lastPage(),
            ]
        ]);
    }
}
