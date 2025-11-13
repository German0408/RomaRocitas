<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanDuplicateFeatures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-duplicate-features';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate features from options, keeping only one of each unique value per option';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Finding duplicate features...');

        $options = \App\Models\Option::all();
        $totalRemoved = 0;

        foreach ($options as $option) {
            $features = $option->features;
            
            if ($features->isEmpty()) {
                continue;
            }

            // Group features by value to find duplicates
            $featuresByValue = $features->groupBy('value');
            $duplicatesFound = false;

            foreach ($featuresByValue as $value => $featureGroup) {
                if ($featureGroup->count() > 1) {
                    $duplicatesFound = true;
                    // Keep the first feature, delete the rest
                    $keepFeature = $featureGroup->first();
                    $duplicateFeatures = $featureGroup->skip(1);

                    $this->line("Option {$option->name}: Removing " . $duplicateFeatures->count() . " duplicate features for value '{$value}'");

                    foreach ($duplicateFeatures as $duplicate) {
                        $duplicate->delete();
                        $totalRemoved++;
                    }
                }
            }

            if ($duplicatesFound) {
                $this->line("Option {$option->name}: Cleaned up duplicates");
            }
        }

        $this->info("Cleanup complete! Removed {$totalRemoved} duplicate features.");
    }
}
