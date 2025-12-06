<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SeatHoldService;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredSeatHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seats:release-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired seat holds (runs every minute via cron)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $seatHoldService = app(SeatHoldService::class);
            $count = $seatHoldService->cleanupExpiredHolds();

            if ($count > 0) {
                $this->info("Released {$count} expired seat holds");
                Log::info("Released {$count} expired seat holds");
            } else {
                $this->info("No expired seat holds to release");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error releasing expired seat holds: " . $e->getMessage());
            Log::error("Error releasing expired seat holds", [
                'error' => $e->getMessage()
            ]);
            return Command::FAILURE;
        }
    }
}
