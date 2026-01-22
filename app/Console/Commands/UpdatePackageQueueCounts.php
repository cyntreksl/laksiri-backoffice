<?php

namespace App\Console\Commands;

use App\Models\HBL;
use App\Models\HBLPackage;
use App\Models\PackageQueue;
use Illuminate\Console\Command;

class UpdatePackageQueueCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-queue:update-counts {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update package queue counts and set HBL package release status to "held" for packages in bond area';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made');
        }

        $this->info('Updating package queue counts and HBL package statuses...');

        // Step 1: Update HBL packages in bond area to "held" status
        $this->info('Step 1: Setting HBL package release_status to "held" for packages in bond area...');
        
        $pendingPackagesCount = HBLPackage::withoutGlobalScopes()
            ->where('release_status', 'pending')
            ->count();

        $this->info("Found {$pendingPackagesCount} packages with 'pending' status");

        if (!$dryRun && $pendingPackagesCount > 0) {
            HBLPackage::withoutGlobalScopes()
                ->where('release_status', 'pending')
                ->update(['release_status' => 'held']);
            
            $this->info("✓ Updated {$pendingPackagesCount} packages to 'held' status");
        }

        // Step 2: Update package queue counts
        $this->info('Step 2: Updating package queue counts...');
        
        $packageQueues = PackageQueue::with('token')->get();
        $this->info("Found {$packageQueues->count()} package queues to process");

        $bar = $this->output->createProgressBar($packageQueues->count());
        $bar->start();

        $updated = 0;
        foreach ($packageQueues as $queue) {
            // Get the HBL for this queue
            $hbl = HBL::withoutGlobalScopes()
                ->where('reference', $queue->reference)
                ->first();

            if (!$hbl) {
                $bar->advance();
                continue;
            }

            // Get all packages for this HBL
            $allPackages = $hbl->packages()->withoutGlobalScopes()->get();
            $totalPackages = $allPackages->count();
            
            if ($totalPackages === 0) {
                $bar->advance();
                continue;
            }
            
            // Count released packages
            $releasedPackages = $allPackages->where('release_status', 'released')->count();
            $heldPackages = $totalPackages - $releasedPackages;

            // Determine status
            $status = 'pending';
            $isReleased = false;
            $completedAt = null;

            if ($releasedPackages > 0 && $heldPackages > 0) {
                $status = 'partial';
            } elseif ($releasedPackages > 0 && $heldPackages === 0) {
                $status = 'completed';
                $isReleased = true;
                $completedAt = $queue->released_at ?? now();
            }

            // Build released_packages array
            $releasedPackagesArray = [];
            foreach ($allPackages->where('release_status', 'released') as $pkg) {
                $releasedPackagesArray[$pkg->id] = "{$pkg->package_type} (Released)";
            }

            if (!$dryRun) {
                $queue->update([
                    'released_package_count' => $releasedPackages,
                    'held_package_count' => $heldPackages,
                    'status' => $status,
                    'is_released' => $isReleased,
                    'completed_at' => $completedAt,
                    'released_packages' => $releasedPackagesArray,
                ]);
                $updated++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("DRY RUN completed. Would have updated {$packageQueues->count()} package queues");
        } else {
            $this->info("✓ Successfully updated {$updated} package queues");
        }

        $this->newLine();
        $this->info('Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['HBL Packages set to "held"', $pendingPackagesCount],
                ['Package Queues processed', $packageQueues->count()],
                ['Package Queues updated', $updated],
            ]
        );

        return Command::SUCCESS;
    }
}
