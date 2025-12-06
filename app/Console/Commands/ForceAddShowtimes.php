<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Phim;
use App\Models\PhongChieu;
use App\Models\SuatChieu;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ForceAddShowtimes extends Command
{
    protected $signature = 'showtimes:force-add {--days=3 : Number of days}';
    protected $description = 'Force add showtimes (skip conflict check)';

    public function handle()
    {
        $days = (int) $this->option('days');
        
        $movies = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])->limit(3)->get();
        
        if ($movies->isEmpty()) {
            $movies = Phim::limit(3)->get();
        }
        
        if ($movies->isEmpty()) {
            $this->error('No movies found.');
            return 1;
        }
        
        $rooms = PhongChieu::where('trang_thai', 1)->get();
        
        if ($rooms->isEmpty()) {
            $this->error('No active rooms found.');
            return 1;
        }
        
        $this->info("Adding showtimes for {$movies->count()} movies in {$rooms->count()} rooms for next {$days} days...");
        
        $now = Carbon::now();
        $createdCount = 0;
        
        $timeSlots = [
            '10:00', '13:00', '16:00', '19:00', '22:00'
        ];
        
        foreach ($movies as $movie) {
            $movieDuration = $movie->do_dai ?? 120;
            
            for ($day = 1; $day <= $days; $day++) {
                $currentDate = $now->copy()->addDays($day)->startOfDay();
                
                foreach ($timeSlots as $time) {
                    $startTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $time);
                    $endTime = $startTime->copy()->addMinutes($movieDuration + 15);
                    
                    foreach ($rooms as $room) {
                        try {
                            $exists = SuatChieu::where('id_phim', $movie->id)
                                ->where('id_phong', $room->id)
                                ->where('thoi_gian_bat_dau', $startTime)
                                ->exists();
                            
                            if (!$exists) {
                                SuatChieu::create([
                                    'id_phim' => $movie->id,
                                    'id_phong' => $room->id,
                                    'thoi_gian_bat_dau' => $startTime,
                                    'thoi_gian_ket_thuc' => $endTime,
                                    'trang_thai' => 1
                                ]);
                                
                                $createdCount++;
                                $this->line("✓ {$movie->ten_phim} - {$room->ten_phong} - {$startTime->format('d/m/Y H:i')}");
                            }
                        } catch (\Exception $e) {
                            $this->warn("⚠ Error: " . $e->getMessage());
                        }
                    }
                }
            }
        }
        
        $this->info("\n✅ Created {$createdCount} showtimes!");
        return 0;
    }
}

