<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanDuplicateOptionsTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-duplicate-options-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate options from the options table, keeping only one of each name/type combination';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Finding duplicate options...');

        // Get all unique name/type combinations that have duplicates
        $duplicateGroups = \App\Models\Option::select('name', 'type')
            ->groupBy('name', 'type')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicateGroups->isEmpty()) {
            $this->info('No duplicate options found.');
            return;
        }

        $this->info("Found {$duplicateGroups->count()} option types with duplicates.");

        $totalRemoved = 0;

        foreach ($duplicateGroups as $group) {
            // Get all options with this name/type combination
            $options = \App\Models\Option::where('name', $group->name)
                ->where('type', $group->type)
                ->orderBy('id')
                ->get();

            // Keep the first one (smallest ID)
            $keepOption = $options->first();
            $duplicateOptions = $options->skip(1);

            $this->line("Processing {$group->name} (Type: {$group->type}): Keeping ID {$keepOption->id}, removing " . $duplicateOptions->count() . " duplicates");

        foreach ($duplicateGroups as $group) {
            // Get all options with this name/type combination
            $options = \App\Models\Option::where('name', $group->name)
                ->where('type', $group->type)
                ->orderBy('id')
                ->get();

            // Keep the first one (smallest ID)
            $keepOption = $options->first();
            $duplicateOptions = $options->skip(1);

            $this->line("Processing {$group->name} (Type: {$group->type}): Keeping ID {$keepOption->id}, removing " . $duplicateOptions->count() . " duplicates");

            foreach ($duplicateOptions as $duplicate) {
                // Move features from duplicate to kept option
                \App\Models\Feature::where('option_id', $duplicate->id)
                    ->update(['option_id' => $keepOption->id]);

                // Update pivot table to point to the kept option
                \Illuminate\Support\Facades\DB::table('option_product')
                    ->where('option_id', $duplicate->id)
                    ->update(['option_id' => $keepOption->id]);

                // Delete the duplicate option
                $duplicate->delete();
                $totalRemoved++;
            }
        }
        }

        $this->info("Cleanup complete! Removed {$totalRemoved} duplicate options.");
        $this->info('Pivot table references have been updated to point to the kept options.');
    }
}
