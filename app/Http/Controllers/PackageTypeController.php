<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackageTypeRequest;
use App\Http\Requests\UpdatePackageTypeRequest;
use App\Interfaces\PackageTypeRepositoryInterface;
use App\Models\PackageType;
use Inertia\Inertia;

class PackageTypeController extends Controller
{
    public function __construct(
        private readonly PackageTypeRepositoryInterface $packageTypeRepository,
    ) {}

    public function index()
    {
        return Inertia::render('Setting/PackageType/PackageTypeList', [
            'packageTypes' => $this->packageTypeRepository->getPackageTypes(),
        ]);
    }

    public function store(StorePackageTypeRequest $request)
    {
        $this->packageTypeRepository->storePackageType($request->all());
    }

    public function update(UpdatePackageTypeRequest $request, PackageType $packageType)
    {
        $this->packageTypeRepository->updatePackageType($request->all(), $packageType);
    }

    public function destroy(PackageType $packageType)
    {
        $this->packageTypeRepository->destroyPackageType($packageType);
    }
}
