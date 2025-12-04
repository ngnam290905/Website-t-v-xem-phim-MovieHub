<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Phim;
use App\Models\PhongChieu;
use App\Models\SuatChieu;
use Carbon\Carbon;

class CreateShowtimesNow extends Command
{
    protected $signature = 'showtimes:create-now {--days=7 : Number of days to create showtimes for}';
    protected $description = 'Create showtimes starting from now for the next N days';

    public function handle()
    {
        $days = (int) $this->option('days');
        
        $movies = Phim::where('trang_thai', 'dang_chieu')->get();
        
        if ($movies->isEmpty()) {
            $this->error('No movies with status "dang_chieu" found.');
            return 1;
        }
        
        $rooms = PhongChieu::where('trang_thai', 1)->get();
        
        if ($rooms->isEmpty()) {
            $this->error('No active rooms found.');
            return 1;
        }
        
        $this->info("Found {$movies->count()} movies and {$rooms->count()} rooms");
        $this->info("Creating showtimes starting from now for the next {$days} days...");
        
        $createdCount = 0;
        $now = Carbon::now();
        
        foreach ($movies as $movie) {
            $movieDuration = $movie->do_dai ?? 120;
            
            for ($day = 0; $day < $days; $day++) {
                $currentDate = $now->copy()->addDays($day);
                
                if ($day === 0) {
                    $startHour = max(16, $now->hour + 1);
                    $timeSlots = $this->getTimeSlotsForToday($startHour);
                } else {
                    $timeSlots = [
                        ['start' => 9, 'end' => 11],
                        ['start' => 12, 'end' => 14],
                        ['start' => 15, 'end' => 17],
                        ['start' => 18, 'end' => 20],
                        ['start' => 21, 'end' => 23],
                    ];
                }
                
                foreach ($rooms as $room) {
                    foreach ($timeSlots as $slot) {
                        $startTime = $currentDate->copy()->setTime($slot['start'], 0);
                        $endTime = $startTime->copy()->addMinutes($movieDuration);
                        
                        if ($startTime->isPast()) {
                            continue;
                        }
                        
                        $conflict = SuatChieu::where('id_phong', $room->id)
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
                            $this->line("Created: {$movie->ten_phim} - {$room->ten_phong} - {$startTime->format('d/m/Y H:i')}");
                        }
                    }
                }
            }
        }
        
        $this->info("\n✅ Successfully created {$createdCount} showtimes!");
        return 0;
    }
    
    private function getTimeSlotsForToday($startHour)
    {
        $slots = [];
        $currentHour = $startHour;
        
        while ($currentHour < 23) {
            $endHour = min($currentHour + 2, 23);
            $slots[] = ['start' => $currentHour, 'end' => $endHour];
            $currentHour = $endHour + 1;
        }
        
        return $slots;
    }
}

