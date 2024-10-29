<?php

namespace App\Interfaces\Api;

interface PackageTypeRepositoryInterface
{
    /**
     * Retrieve Package.
     *
     * @method  GET api.laksiri.world/v1/package-type-list
     */
    public function getPackageTypes();
}
