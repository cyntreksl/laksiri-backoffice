<?php

namespace App\Actions\Loading\LoadedContainer;

use App\Models\LoadedContainer;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDraftLoadedContainer
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(LoadedContainer $loadedContainer)
    {
        try {
            $loadedContainer->forceDelete();
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete draft loaded container: '.$e->getMessage());
        }
    }
}
