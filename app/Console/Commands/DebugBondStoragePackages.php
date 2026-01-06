<?php

namespace App\Console\Commands;

use App\Models\Container;
use Illuminate\Console\Command;

class DebugBondStoragePackages extends Command
{
    protected $signature = 'bond-storage:debug {container_id?}';
    protected $description = 'Debug bond storage package retrieval';

    public function handle()
    {
        $containerId = $this->argument('container_id');

        if (!$containerId) {
            // Show all containers with REACHED DESTINATION or UNLOADED status
            $containers = Container::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->whereIn('status', ['REACHED DESTINATION', 'UNLOADED'])
                ->orderBy('created_at', 'desc')
                ->get(['id', 'reference', 'cargo_type', 'status']);

            if ($containers->isEmpty()) {
                $this->error('No containers found with status REACHED DESTINATION or UNLOADED');
                return;
            }

            $this->info('Available containers:');
            $this->table(
                ['ID', 'Reference', 'Cargo Type', 'Status'],
                $containers->map(fn($c) => [$c->id, $c->reference, $c->cargo_type, $c->status])
            );

            $containerId = $this->ask('Enter container ID to debug');
        }

        $container = Container::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)->find($containerId);

        if (!$container) {
            $this->error("Container {$containerId} not found");
            return;
        }

        $this->info("Container: {$container->reference} ({$container->cargo_type})");
        $this->info("Status: {$container->status}");
        $this->newLine();

        // Check all packages
        $allPackages = $container->hbl_packages;
        $this->info("Total packages in container: {$allPackages->count()}");

        if ($allPackages->isEmpty()) {
            $this->warn('No packages found in this container');
            return;
        }

        // Check packages by status
        $draftUnloadPackages = $container->hbl_packages()
            ->wherePivot('status', 'draft-unload')
            ->get();
        $this->info("Packages with draft-unload status: {$draftUnloadPackages->count()}");

        $unloadedPackages = $container->hbl_packages()
            ->where('is_unloaded', true)
            ->get();
        $this->info("Packages with is_unloaded=true: {$unloadedPackages->count()}");

        // Check packages without bond numbers
        $withoutBondNumbers = $allPackages->filter(function ($pkg) {
            return empty($pkg->bond_storage_number);
        });
        $this->info("Packages without bond storage numbers: {$withoutBondNumbers->count()}");

        $withBondNumbers = $allPackages->filter(function ($pkg) {
            return !empty($pkg->bond_storage_number);
        });
        $this->info("Packages with bond storage numbers: {$withBondNumbers->count()}");

        $this->newLine();

        // Show sample packages
        if ($allPackages->count() > 0) {
            $this->info('Sample packages:');
            $sampleData = $allPackages->take(5)->map(function ($pkg) {
                return [
                    'ID' => $pkg->id,
                    'HBL' => $pkg->hbl->hbl_number ?? 'N/A',
                    'Type' => $pkg->package_type,
                    'Bond #' => $pkg->bond_storage_number ?: 'None',
                    'Pivot Status' => $pkg->pivot->status ?? 'N/A',
                    'Is Unloaded' => $pkg->is_unloaded ? 'Yes' : 'No',
                ];
            });

            $this->table(
                ['ID', 'HBL', 'Type', 'Bond #', 'Pivot Status', 'Is Unloaded'],
                $sampleData
            );
        }

        // Show what would be returned by GetShipmentPackages
        $this->newLine();
        $this->info('Testing GetShipmentPackages action...');

        try {
            $result = \App\Actions\BondStorage\GetShipmentPackages::run($containerId);
            $this->info("HBL Groups returned: " . count($result['hbl_groups']));
            $this->info("Total packages in result: " . ($result['total_packages'] ?? 0));

            if (!empty($result['hbl_groups'])) {
                $this->info('HBL Groups:');
                foreach ($result['hbl_groups'] as $group) {
                    $packageCount = count($group['packages']);
                    $this->line("  - {$group['hbl_number']}: {$packageCount} package(s)");
                }
            } else {
                $this->warn('No HBL groups returned!');
                $this->newLine();
                $this->info('Possible reasons:');
                $this->line('  1. All packages already have bond storage numbers');
                $this->line('  2. Packages are not in draft-unload status');
                $this->line('  3. Packages have not been unloaded yet');
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
