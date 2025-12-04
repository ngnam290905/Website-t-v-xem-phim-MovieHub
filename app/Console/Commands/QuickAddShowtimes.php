<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Phim;
use App\Models\PhongChieu;
use App\Models\SuatChieu;
use Carbon\Carbon;

class QuickAddShowtimes extends Command
{
    protected $signature = 'showtimes:quick-add {--from-tomorrow : Start from tomorrow}';
    protected $description = 'Quickly add showtimes for today and next few days';

    public function handle()
    {
        $fromTomorrow = $this->option('from-tomorrow');
        
        $movies = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])->get();
        
        if ($movies->isEmpty()) {
            $movies = Phim::limit(5)->get();
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
        
        $this->info("Found {$movies->count()} movies and {$rooms->count()} rooms");
        
        $now = Carbon::now();
        $startDay = $fromTomorrow ? 1 : 0;
        $days = 3;
        $createdCount = 0;
        
        $timeSlots = [
            '09:00', '11:30', '14:00', '16:30', '19:00', '21:30'
        ];
        
        foreach ($movies as $movie) {
            $movieDuration = $movie->do_dai ?? 120;
            
            for ($day = $startDay; $day < $startDay + $days; $day++) {
                $currentDate = $now->copy()->addDays($day)->startOfDay();
                
                foreach ($timeSlots as $time) {
                    $startTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $time);
                    
                    if ($day == 0 && $startTime->lte($now->addMinutes(30))) {
                        continue;
                    }
                    
                    $endTime = $startTime->copy()->addMinutes($movieDuration + 15);
                    
                    foreach ($rooms as $room) {
                        $exists = SuatChieu::where('id_phim', $movie->id)
                            ->where('id_phong', $room->id)
                            ->where('thoi_gian_bat_dau', $startTime)
                            ->exists();
                        
                        if (!$exists) {
                            $conflict = SuatChieu::where('id_phong', $room->id)
                                ->where('trang_thai', 1)
                                ->where(function($query) use ($startTime, $endTime) {
                                    $query->whereBetween('thoi_gian_bat_dau', [$startTime, $endTime])
                                          ->orWhereBetween('thoi_gian_ket_thuc', [$startTime, $endTime])
                                          ->orWhere(function($q) use ($startTime, $endTime) {
                                              $q->where('thoi_gian_bat_dau', '<=', $startTime)
                                                ->where('thoi_gian_ket_thuc', '>=', $endTime);
                                          });
                                })
                                ->exists();
                            
                            if (!$conflict) {
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
                        }
                    }
                }
            }
        }
        
        $this->info("\n✅ Created {$createdCount} showtimes!");
        return 0;
    }
}

