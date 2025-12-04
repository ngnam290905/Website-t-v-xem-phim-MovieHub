<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Phim;
use App\Models\PhongChieu;
use App\Models\SuatChieu;
use Carbon\Carbon;

class AddShowtimes extends Command
{
    protected $signature = 'showtimes:add {--days=7 : Number of days to create showtimes for} {--start-hour=9 : Starting hour}';
    protected $description = 'Add showtimes for movies starting from now';

    public function handle()
    {
        $days = (int) $this->option('days');
        $startHour = (int) $this->option('start-hour');
        
        $movies = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
            ->orWhere('trang_thai', 1)
            ->get();
            
        if ($movies->isEmpty()) {
            $movies = Phim::all();
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
        $this->info("Adding showtimes for the next {$days} days...");
        
        $now = Carbon::now();
        $createdCount = 0;
        $conflictCount = 0;
        
        foreach ($movies as $movie) {
            $movieDuration = $movie->do_dai ?? 120;
            
            for ($day = 0; $day < $days; $day++) {
                $currentDate = $now->copy()->addDays($day)->startOfDay();
                
                $timeSlots = [];
                if ($day == 0) {
                    $currentHour = $now->hour;
                    $currentMinute = $now->minute;
                    
                    if ($currentHour < 10 || ($currentHour == 10 && $currentMinute < 30)) {
                        $timeSlots = ['10:30', '13:00', '15:30', '18:00', '20:30'];
                    } elseif ($currentHour < 13 || ($currentHour == 13 && $currentMinute < 15)) {
                        $timeSlots = ['13:15', '15:45', '18:15', '20:45'];
                    } elseif ($currentHour < 16 || ($currentHour == 16 && $currentMinute < 15)) {
                        $timeSlots = ['16:15', '18:45', '21:15'];
                    } elseif ($currentHour < 19 || ($currentHour == 19 && $currentMinute < 15)) {
                        $timeSlots = ['19:15', '21:45'];
                    } elseif ($currentHour < 21 || ($currentHour == 21 && $currentMinute < 30)) {
                        $timeSlots = ['21:30'];
                    } else {
                        continue;
                    }
                } else {
                    $timeSlots = ['09:00', '11:30', '14:00', '16:30', '19:00', '21:30'];
                }
                
                foreach ($timeSlots as $time) {
                    foreach ($rooms as $room) {
                        $startTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $time);
                        
                        if ($startTime->lte($now)) {
                            continue;
                        }
                        
                        $endTime = $startTime->copy()->addMinutes($movieDuration + 15);
                        
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
                            $this->line("✓ Created: {$movie->ten_phim} - {$room->ten_phong} - {$startTime->format('d/m/Y H:i')}");
                        } else {
                            $conflictCount++;
                        }
                    }
                }
            }
        }
        
        $this->info("\n✅ Successfully created {$createdCount} showtimes!");
        if ($conflictCount > 0) {
            $this->warn("⚠ Skipped {$conflictCount} showtimes due to conflicts.");
        }
        
        return 0;
    }
}

