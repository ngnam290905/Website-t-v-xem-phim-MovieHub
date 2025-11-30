<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearAllBookings extends Command
{
    protected $signature = 'bookings:clear';
    protected $description = 'Clear all booking data (dat_ve, chi_tiet_dat_ve, chi_tiet_combo, thanh_toan, suat_chieu_ghe)';

    public function handle()
    {
        if (!$this->confirm('Are you sure you want to delete ALL booking data? This action cannot be undone!')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        try {
            DB::beginTransaction();
            
            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Delete related data first (foreign key constraints)
            $this->info('Deleting payment records...');
            if (Schema::hasTable('thanh_toan')) {
                DB::table('thanh_toan')->delete();
                $this->info('✓ Payment records deleted');
            }

            $this->info('Deleting booking seat details...');
            if (Schema::hasTable('chi_tiet_dat_ve')) {
                DB::table('chi_tiet_dat_ve')->delete();
                $this->info('✓ Booking seat details deleted');
            }

            $this->info('Deleting booking combo details...');
            if (Schema::hasTable('chi_tiet_combo')) {
                DB::table('chi_tiet_combo')->delete();
                $this->info('✓ Booking combo details deleted');
            }

            // Also check for alternative table name
            if (Schema::hasTable('chi_tiet_dat_ve_combo')) {
                DB::table('chi_tiet_dat_ve_combo')->delete();
                $this->info('✓ Booking combo details (alternative table) deleted');
            }

            $this->info('Deleting showtime seat statuses...');
            if (Schema::hasTable('suat_chieu_ghe')) {
                DB::table('suat_chieu_ghe')->delete();
                $this->info('✓ Showtime seat statuses deleted');
            }

            // Delete main booking table
            $this->info('Deleting bookings...');
            if (Schema::hasTable('dat_ve')) {
                DB::table('dat_ve')->delete();
                $this->info('✓ Bookings deleted');
            }

            // Reset seat status to available
            $this->info('Resetting seat statuses to available...');
            if (Schema::hasTable('ghe')) {
                DB::table('ghe')->update(['trang_thai' => 1]);
                $this->info('✓ Seat statuses reset to available');
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            DB::commit();

            $this->info('');
            $this->info('✓ All booking data has been cleared successfully!');
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error clearing booking data: ' . $e->getMessage());
            return 1;
        }
    }
}
