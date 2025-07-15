<?php

namespace App\Console\Commands;

use App\Actions\HBL\HBLCharges\CalculateDemurrageCharge;
use App\Actions\HBL\HBLCharges\UpdateHBLDestinationCharges;
use App\Models\HBL;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateDemurrageChargeSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hbl:calculate-demurrage-charge-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and update HBL demurrage charges and update destination charges if not already present, otherwise update existing.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting HBL demurrage charge calculation and destination charge update...');
        $count = 0;
        $skipped = 0;
        $updated = 0;
        $batchSize = 100;

        HBL::with(['packages', 'branch', 'destinationCharge', 'containers'])
            ->chunk($batchSize, function ($hbls) use (&$count, &$skipped, &$updated) {
                foreach ($hbls as $hbl) {
                    if ($hbl->destinationCharge) {
                        // Update existing destination charge
                        try {
                            UpdateHBLDestinationCharges::run($hbl);
                            $updated++;
                        } catch (\Throwable $e) {
                            Log::error('Failed to update destination charge for HBL ID '.$hbl->id.': '.$e->getMessage());
                        }
                        $skipped++;

                        continue;
                    }
                    try {
                        // Calculate demurrage charge (side effect: UpdateHBLDestinationCharges will use it)
                        CalculateDemurrageCharge::run($hbl);
                        UpdateHBLDestinationCharges::run($hbl);
                        $count++;
                    } catch (\Throwable $e) {
                        Log::error('Failed to calculate demurrage/destination charge for HBL ID '.$hbl->id.': '.$e->getMessage());
                    }
                }
            });

        $this->info("Calculation complete. New destination charges: {$count}, Updated: {$updated}, Skipped: {$skipped}");

        return 0;
    }
}
