<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\HandlingProcedure;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;

class HandlingProcedureController extends Controller
{
    public function index(Request $request)
    {
        $container = Container::withoutGlobalScope(BranchScope::class)->whereId($request->container)->first();

        // Get latest status for each step
        return $container->handlingProcedures()
            ->with('completedBy')
            ->whereIn('id', function ($query) use ($container) {
                $query->select(\DB::raw('MAX(id)'))
                    ->from('handling_procedures')
                    ->where('container_id', $container->id)
                    ->groupBy('step_id');
            })
            ->get();
    }

    public function store(Request $request)
    {
        $container = Container::withoutGlobalScopes()->find($request->container);

        $validated = $request->validate([
            'step_id' => 'required|integer',
            'is_completed' => 'required|boolean',
        ]);

        // Always create a new record instead of updating
        $procedure = HandlingProcedure::create([
            'container_id' => $container->id,
            'step_id' => $validated['step_id'],
            'is_completed' => $validated['is_completed'],
            'completed_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        return $procedure->load('completedBy');
    }
}
