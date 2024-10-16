<?php

namespace App\Actions\PackagePrice;

use App\Models\PackagePrice;
use Lorisleiva\Actions\Concerns\AsAction;

class DeletePackagePriceRule
{
    use AsAction;

    /**
     * Handle the deletion of a package price rule.
     *
     * This method deletes the specified `PackagePrice` from the database.
     *
     * @param  PackagePrice  $packagePrice  The package price rule to be deleted.
     * @return void
     */
    public function handle(PackagePrice $packagePrice): void
    {
        $packagePrice->delete();
    }
}
