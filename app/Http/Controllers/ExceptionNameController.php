<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExceptionNameRequest;
use App\Http\Requests\UpdateExceptionNameRequest;
use App\Interfaces\ExceptionNameRepositoryInterface;
use App\Models\ExceptionName;
use Inertia\Inertia;

class ExceptionNameController extends Controller
{
    public function __construct(
        private readonly ExceptionNameRepositoryInterface $exceptionNameRepository,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Setting/ExceptionNames/ExceptionNameList', [
            'exceptionNames' => $this->exceptionNameRepository->getExceptionNames(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExceptionNameRequest $request)
    {
        $this->exceptionNameRepository->storeExceptionName($request->all());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExceptionNameRequest $request, ExceptionName $exceptionName)
    {
        $this->exceptionNameRepository->updateExceptionName($request->all(), $exceptionName);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExceptionName $exceptionName)
    {
        return Inertia::render('Setting/ExceptionNames/UpdateExceptionNameForm', [
            'exceptionName' => $exceptionName,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExceptionName $exceptionName)
    {
        $this->exceptionNameRepository->destroyExceptionName($exceptionName);
    }
}
