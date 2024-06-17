<?php

namespace App\Observers;

use App\Models\HBL;

class HBLObserver
{
    protected static array $cascade_relations = ['packages', 'status', 'hblPayment'];

    /**
     * Handle the HBL "created" event.
     */
    public function created(HBL $hBL): void
    {
        //
    }

    /**
     * Handle the HBL "updated" event.
     */
    public function updated(HBL $hBL): void
    {
        //
    }

    /**
     * Handle the HBL "deleted" event.
     */
    public function deleted(HBL $hBL): void
    {
        foreach (static::$cascade_relations as $relation) {
            foreach ($hBL->{$relation}()->get() as $item) {
                $item->delete();
            }
        }
    }

    /**
     * Handle the HBL "restored" event.
     */
    public function restored(HBL $hBL): void
    {
        foreach (static::$cascade_relations as $relation) {
            foreach ($hBL->{$relation}()->withTrashed()->get() as $item) {
                $item->restore();
            }
        }
    }

    /**
     * Handle the HBL "force deleted" event.
     */
    public function forceDeleted(HBL $hBL): void
    {
        //
    }
}
