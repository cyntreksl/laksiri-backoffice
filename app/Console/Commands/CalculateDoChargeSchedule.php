<?php

namespace App\Console\Commands;

use App\Actions\HBL\HBLCharges\UpdateHBLDestinationCharges;
use App\Models\HBL;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateDoChargeSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hbl:calculate-do-charge-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and update HBL destination charges if not already present, otherwise update existing.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting HBL destination charge calculation and update...');
        $count = 0;
        $skipped = 0;
        $updated = 0;
        $batchSize = 100;

        // Fix: Use withoutGlobalScopes() to process all HBLs regardless of branch context
        HBL::withoutGlobalScopes()
            ->with(['packages', 'branch', 'destinationCharge'])
            ->chunk($batchSize, function ($hbls) use (&$count, &$skipped, &$updated) {
                foreach ($hbls as $hbl) {
                    if ($hbl->destinationCharge) {
                        // Update existing destination charge
                        try {
                            UpdateHBLDestinationCharges::run($hbl);
                            $updated++;
                        } catch (\Throwable $e) {
                            Log::error('Failed to update destination charge for HBL ID '.$hbl->id.': '.$e->getMessage());
                            $skipped++;
                        }

                        continue;
                    }
                    try {
                        UpdateHBLDestinationCharges::run($hbl);
                        $count++;
                    } catch (\Throwable $e) {
                        Log::error('Failed to calculate destination charge for HBL ID '.$hbl->id.': '.$e->getMessage());
                        $skipped++;
                    }
                }
            });

        $this->info("Calculation complete. New destination charges: {$count}, Updated: {$updated}, Skipped: {$skipped}");

        return 0;
    }
}
