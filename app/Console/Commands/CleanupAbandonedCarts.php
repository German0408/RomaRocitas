<?php

namespace App\Console\Commands;

use App\Models\Cart;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupAbandonedCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:cleanup-abandoned {--days=30 : Number of days to consider carts abandoned}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up abandoned carts that are older than specified days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning up carts abandoned before: {$cutoffDate}");

        // Delete carts that are older than the cutoff and belong to guests (no user_id) or have no recent activity
        $deletedCount = Cart::where('updated_at', '<', $cutoffDate)
            ->where(function ($query) {
                $query->whereNull('user_id')
                      ->orWhereDoesntHave('user'); // In case user was deleted
            })
            ->delete();

        $this->info("Deleted {$deletedCount} abandoned carts.");

        return Command::SUCCESS;
    }
}
