<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SeatHoldService;
use App\Services\BookingService;

class CleanupExpiredLocks extends Command
{
    protected $signature = 'booking:cleanup-expired';
    protected $description = 'Cleanup expired seat locks and bookings';

    public function __construct(
        private SeatHoldService $seatLockService,
        private BookingService $bookingService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting cleanup...');
        
        $deletedLocks = $this->seatLockService->cleanupExpiredHolds();
        $this->info("Deleted {$deletedLocks} expired seat locks");
        
        $expiredBookings = $this->bookingService->expireOldBookings();
        $this->info("Expired {$expiredBookings} old bookings");
        
        $this->info('Cleanup completed!');
        
        return Command::SUCCESS;
    }
}

