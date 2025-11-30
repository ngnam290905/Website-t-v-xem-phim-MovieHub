<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShowtimeSeat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReleaseExpiredSeats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seats:release-expired {--showtime= : Specific showtime ID to release seats for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired holding seats (seats that were held but payment was not completed)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!Schema::hasTable('suat_chieu_ghe')) {
            $this->warn('Table suat_chieu_ghe does not exist. Skipping...');
            return Command::SUCCESS;
        }
        
        $this->info('Starting release of expired holding seats...');
        
        $showtimeId = $this->option('showtime');
        
        try {
            $releasedCount = ShowtimeSeat::releaseExpiredSeats($showtimeId);
            
            if ($releasedCount > 0) {
                $this->info("✅ Released {$releasedCount} expired seat(s).");
                Log::info('Expired seats released', [
                    'count' => $releasedCount,
                    'showtime_id' => $showtimeId
                ]);
            } else {
                $this->info('✅ No expired seats found.');
            }
            
            // Also check for seats that are holding but hold_expires_at is in the past
            $now = Carbon::now();
            $expiredHoldingSeats = ShowtimeSeat::where('status', 'holding')
                ->whereNotNull('hold_expires_at')
                ->where('hold_expires_at', '<', $now)
                ->get();
            
            if ($expiredHoldingSeats->count() > 0) {
                $additionalCount = 0;
                foreach ($expiredHoldingSeats as $seat) {
                    $seat->status = 'available';
                    $seat->hold_expires_at = null;
                    $seat->save();
                    $additionalCount++;
                }
                
                if ($additionalCount > 0) {
                    $this->info("✅ Released {$additionalCount} additional expired seat(s).");
                    Log::info('Additional expired seats released', [
                        'count' => $additionalCount
                    ]);
                }
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error releasing expired seats: ' . $e->getMessage());
            Log::error('Error releasing expired seats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}
