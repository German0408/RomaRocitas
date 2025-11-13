<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateOptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-duplicate-options';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate option attachments from products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Finding duplicate option attachments...');

        // Find all duplicate option attachments
        $duplicates = DB::table('option_product')
            ->select('product_id', 'option_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as count'))
            ->groupBy('product_id', 'option_id')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate option attachments found.');
            return;
        }

        $this->info("Found {$duplicates->count()} duplicate option groups to clean up.");

        $totalRemoved = 0;

        foreach ($duplicates as $duplicate) {
            // Keep the first occurrence, delete the rest
            $removed = DB::table('option_product')
                ->where('product_id', $duplicate->product_id)
                ->where('option_id', $duplicate->option_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();

            $totalRemoved += $removed;

            $this->line("Product {$duplicate->product_id}, Option {$duplicate->option_id}: Removed {$removed} duplicates");
        }

        $this->info("Cleanup complete! Removed {$totalRemoved} duplicate option attachments.");
    }
}
