<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Interfaces\Api\PackageTypeRepositoryInterface;

class PackageTypeController extends Controller
{
    public function __construct(
        private readonly PackageTypeRepositoryInterface $packageTypeRepository,
    ) {
    }

    /**
     * Get pending pickups for driver
     *
     * Display the pending pickups for the authenticated driver.
     *
     * @group Pickups
     */
    public function index()
    {
        return $this->packageTypeRepository->getPackageTypes();
    }
}
