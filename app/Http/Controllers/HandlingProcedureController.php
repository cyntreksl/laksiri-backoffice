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
        return $container->handlingProcedures()
            ->with('completedBy')
            ->get();
    }

    public function store(Request $request)
    {
        $container = Container::withoutGlobalScopes()->find($request->container);

        $validated = $request->validate([
            'step_id' => 'required|integer',
            'is_completed' => 'required|boolean'
        ]);

        $procedure = HandlingProcedure::updateOrCreate(
            [
                'container_id' => $container->id,
                'step_id' => $validated['step_id']
            ],
            [
                'is_completed' => $validated['is_completed'],
                'completed_by' => $validated['is_completed'] ? auth()->id() : null,
                'completed_at' => $validated['is_completed'] ? now() : null
            ]
        );

        return $procedure->load('completedBy');
    }
}
