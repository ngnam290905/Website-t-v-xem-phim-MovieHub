<?php

namespace App\Console\Commands;

use App\Models\DatVe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCancelExpiredBookings extends Command
{
    protected $signature = 'bookings:auto-cancel-expired';
    protected $description = 'Auto cancel offline bookings that expired after 5 minutes';

    public function handle()
    {
        $this->info('Checking for expired offline bookings...');
        
        $expiredQuery = DatVe::with(['chiTietDatVe.ghe'])
            ->where('trang_thai', 0); // pending
        
        // Check if phuong_thuc_thanh_toan column exists before filtering
        if (\Illuminate\Support\Facades\Schema::hasColumn('dat_ve', 'phuong_thuc_thanh_toan')) {
            $expiredQuery->where('phuong_thuc_thanh_toan', 2); // offline payment
        }
        // If column doesn't exist, we'll check all pending bookings (backward compatibility)
        
        $expiredBookings = $expiredQuery->where(function($query) {
                // Check if expires_at column exists
                if (\Illuminate\Support\Facades\Schema::hasColumn('dat_ve', 'expires_at')) {
                    $query->where('expires_at', '<=', now())
                          ->orWhere(function($q) {
                              // Fallback: check created_at + 5 minutes if expires_at is null
                              $q->whereNull('expires_at')
                                ->where('created_at', '<=', now()->subMinutes(5));
                          });
                } else {
                    // Fallback: use created_at + 5 minutes
                    $query->where('created_at', '<=', now()->subMinutes(5));
                }
            })
            ->get();
        
        $count = 0;
        
        foreach ($expiredBookings as $booking) {
            try {
                DB::transaction(function () use ($booking) {
                    // Cancel booking
                    $booking->update(['trang_thai' => 2]);
                    
                    // Release seats
                    foreach ($booking->chiTietDatVe as $detail) {
                        if ($detail->ghe) {
                            $detail->ghe->update(['trang_thai' => 1]);
                        }
                    }
                    
                    // Release showtime seats if table exists
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('suat_chieu_ghe')) {
                            \App\Models\ShowtimeSeat::where('id_suat_chieu', $booking->id_suat_chieu)
                                ->whereIn('id_ghe', $booking->chiTietDatVe->pluck('id_ghe'))
                                ->where('trang_thai', 'booked')
                                ->delete();
                        }
                    } catch (\Throwable $e) {
                        // Ignore if table doesn't exist
                    }
                });
                
                $count++;
                $this->line("Cancelled booking #{$booking->id}");
            } catch (\Throwable $e) {
                Log::error("Error auto-cancelling booking #{$booking->id}: " . $e->getMessage());
                $this->error("Failed to cancel booking #{$booking->id}: " . $e->getMessage());
            }
        }
        
        if ($count > 0) {
            $this->info("Successfully cancelled {$count} expired booking(s).");
        } else {
            $this->info("No expired bookings found.");
        }
        
        return Command::SUCCESS;
    }
}
