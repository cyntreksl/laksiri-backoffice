<?php

namespace App\Http\Controllers;

use App\Interfaces\LoadedContainerRepositoryInterface;
use App\Models\LoadedContainer;
use Illuminate\Http\Request;

class LoadedContainerController extends Controller
{
    public function __construct(
        private readonly LoadedContainerRepositoryInterface $loadedContainerRepository,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->loadedContainerRepository->store($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(LoadedContainer $loadedContainer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LoadedContainer $loadedContainer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LoadedContainer $loadedContainer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LoadedContainer $loadedContainer)
    {
        //
    }
}
