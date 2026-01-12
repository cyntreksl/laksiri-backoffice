<?php

namespace App\Console\Commands;

use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Illuminate\Console\Command;

class FindHBLUnloadedDate extends Command
{
    protected $signature = 'hbl:find-unloaded-date {reference}';

    protected $description = 'Find unloaded dates for an HBL reference';

    public function handle()
    {
        $reference = $this->argument('reference');

        $hbl = HBL::withoutGlobalScope(BranchScope::class)
            ->where('reference', $reference)
            ->orWhere('hbl_number', $reference)
            ->with(['packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class)
                    ->whereNotNull('unloaded_at')
                    ->orderBy('unloaded_at', 'desc');
            }])
            ->first();

        if (! $hbl) {
            $this->error("HBL with reference '{$reference}' not found.");
            return 1;
        }

        $this->info("HBL Reference: {$hbl->reference}");
        $this->info("HBL Number: {$hbl->hbl_number}");
        $this->info("HBL Name: {$hbl->hbl_name}");
        $this->line('');

        $packages = $hbl->packages->whereNotNull('unloaded_at');

        if ($packages->isEmpty()) {
            $this->warn('No packages with unloaded dates found for this HBL.');
            return 0;
        }

        $this->info("Found {$packages->count()} package(s) with unloaded dates:");
        $this->line('');

        $tableData = [];
        foreach ($packages as $package) {
            $tableData[] = [
                'Package ID' => $package->id,
                'Unloaded Date' => $package->unloaded_at ? $package->unloaded_at->format('Y-m-d H:i:s') : 'N/A',
                'Unloaded By' => $package->unloaded_by ?? 'N/A',
                'Is Unloaded' => $package->is_unloaded ? 'Yes' : 'No',
            ];
        }

        $this->table(
            ['Package ID', 'Unloaded Date', 'Unloaded By', 'Is Unloaded'],
            $tableData
        );

        $earliestDate = $packages->min('unloaded_at');
        $latestDate = $packages->max('unloaded_at');

        $this->line('');
        if ($earliestDate) {
            $this->info("Earliest Unloaded Date: {$earliestDate->format('Y-m-d H:i:s')}");
        }
        if ($latestDate && $latestDate != $earliestDate) {
            $this->info("Latest Unloaded Date: {$latestDate->format('Y-m-d H:i:s')}");
        }

        return 0;
    }
}
