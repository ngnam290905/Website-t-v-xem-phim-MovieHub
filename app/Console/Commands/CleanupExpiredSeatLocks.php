<?php

namespace App\Console\Commands;

use App\Services\LegacySeatLockService;
use Illuminate\Console\Command;

class CleanupExpiredSeatLocks extends Command
{
    protected $signature = 'booking:cleanup-locks';
    protected $description = 'Cleanup expired seat locks';

    public function __construct(
        private LegacySeatLockService $seatLockService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Cleaning up expired seat locks...');
        
        $deleted = $this->seatLockService->cleanupExpiredLocks();
        
        $this->info("Cleaned up {$deleted} expired locks.");
        
        return Command::SUCCESS;
    }
}

