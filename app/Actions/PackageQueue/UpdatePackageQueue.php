<?php

namespace App\Actions\PackageQueue;

use App\Models\PackageQueue;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePackageQueue
{
    use AsAction;

    public function handle(array $data, $packageQueue)
    {
        PackageQueue::find($packageQueue)->update($data);
    }
}
