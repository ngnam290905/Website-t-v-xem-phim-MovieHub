<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DatVe;
use App\Models\ChiTietDatVe;
use App\Models\ChiTietCombo;
use App\Models\ThanhToan;
use App\Models\ShowtimeSeat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:cleanup-orphan {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup expired bookings (orphan bookings that were held but never paid)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }
        
        $this->info('Starting cleanup of expired bookings...');
        
        // Find expired bookings: pending status (0), tong_tien = 0, created more than 5 minutes ago
        $expiredTime = Carbon::now()->subMinutes(5);
        
        $expiredBookings = DatVe::where('trang_thai', 0) // pending
            ->where('tong_tien', 0) // never paid
            ->where('created_at', '<', $expiredTime)
            ->get();
        
        $count = $expiredBookings->count();
        
        if ($count === 0) {
            $this->info('✅ No expired bookings found.');
            return Command::SUCCESS;
        }
        
        $this->info("Found {$count} expired booking(s) to cleanup.");
        
        if ($isDryRun) {
            $this->table(
                ['ID', 'Showtime', 'User', 'Created At', 'Age'],
                $expiredBookings->map(function ($booking) {
                    return [
                        $booking->id,
                        $booking->id_suat_chieu ?? 'N/A',
                        $booking->id_nguoi_dung ?? 'Guest',
                        $booking->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
                        $booking->created_at?->diffForHumans() ?? 'N/A',
                    ];
                })->toArray()
            );
            $this->info('Run without --dry-run to actually delete these bookings.');
            return Command::SUCCESS;
        }
        
        $deletedCount = 0;
        $releasedSeatsCount = 0;
        
        DB::transaction(function () use ($expiredBookings, &$deletedCount, &$releasedSeatsCount) {
            foreach ($expiredBookings as $booking) {
                try {
                    // Release seats in suat_chieu_ghe if table exists
                    if (Schema::hasTable('suat_chieu_ghe') && $booking->id_suat_chieu) {
                        $seatDetails = ChiTietDatVe::where('id_dat_ve', $booking->id)->get();
                        
                        foreach ($seatDetails as $detail) {
                            if ($detail->id_ghe) {
                                $showtimeSeat = ShowtimeSeat::where('id_suat_chieu', $booking->id_suat_chieu)
                                    ->where('id_ghe', $detail->id_ghe)
                                    ->first();
                                
                                if ($showtimeSeat && $showtimeSeat->status === 'holding') {
                                    $showtimeSeat->status = 'available';
                                    $showtimeSeat->hold_expires_at = null;
                                    $showtimeSeat->save();
                                    $releasedSeatsCount++;
                                }
                            }
                        }
                    }
                    
                    // Delete related records
                    ChiTietDatVe::where('id_dat_ve', $booking->id)->delete();
                    ChiTietCombo::where('id_dat_ve', $booking->id)->delete();
                    ThanhToan::where('id_dat_ve', $booking->id)->delete();
                    
                    // Delete booking
                    $booking->delete();
                    $deletedCount++;
                    
                } catch (\Exception $e) {
                    Log::error('Error cleaning up booking: ' . $e->getMessage(), [
                        'booking_id' => $booking->id,
                        'error' => $e->getTraceAsString()
                    ]);
                    $this->warn("Failed to cleanup booking #{$booking->id}: {$e->getMessage()}");
                }
            }
        });
        
        $this->info("✅ Cleanup completed!");
        $this->info("   - Deleted {$deletedCount} expired booking(s)");
        $this->info("   - Released {$releasedSeatsCount} seat(s)");
        
        Log::info('Expired bookings cleanup completed', [
            'deleted_bookings' => $deletedCount,
            'released_seats' => $releasedSeatsCount
        ]);
        
        return Command::SUCCESS;
    }
}
