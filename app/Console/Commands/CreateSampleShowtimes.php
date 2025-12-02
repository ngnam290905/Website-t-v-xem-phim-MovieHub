<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Phim;
use App\Models\PhongChieu;
use App\Models\SuatChieu;
use Carbon\Carbon;

class CreateSampleShowtimes extends Command
{
    protected $signature = 'showtimes:create-sample {--days=7 : Number of days to create showtimes for}';
    protected $description = 'Create sample showtimes for movies that are currently showing';

    public function handle()
    {
        $days = (int) $this->option('days');
        
        // Get movies that are currently showing
        $movies = Phim::where(function($query) {
                $query->whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
                      ->orWhere('trang_thai', 1);
            })
            ->get();
            
        // If no movies with those statuses, get any movies
        if ($movies->isEmpty()) {
            $movies = Phim::all();
        }
        
        if ($movies->isEmpty()) {
            $this->error('No movies found. Please create movies first.');
            return 1;
        }
        
        // Get active rooms
        $rooms = PhongChieu::where('trang_thai', 1)->get();
        
        if ($rooms->isEmpty()) {
            $this->error('No active rooms found. Please create rooms first.');
            return 1;
        }
        
        $this->info("Found {$movies->count()} movies and {$rooms->count()} rooms");
        $this->info("Creating showtimes for the next {$days} days...");
        
        $createdCount = 0;
        $startTime = Carbon::now()->startOfDay()->addHours(9); // Start from 9 AM today
        
        foreach ($movies as $movie) {
            $movieDuration = $movie->do_dai ?? 120; // Default 120 minutes if not set
            
            // Create showtimes for each day
            for ($day = 0; $day < $days; $day++) {
                $currentDate = $startTime->copy()->addDays($day);
                
                // Create 4-5 showtimes per day per movie
                $showtimesPerDay = 5;
                $timeSlots = [
                    ['start' => 9, 'end' => 11],   // 9:00 - 11:00
                    ['start' => 12, 'end' => 14],  // 12:00 - 14:00
                    ['start' => 15, 'end' => 17],  // 15:00 - 17:00
                    ['start' => 18, 'end' => 20],  // 18:00 - 20:00
                    ['start' => 21, 'end' => 23],  // 21:00 - 23:00
                ];
                
                // Rotate through rooms
                $roomIndex = ($day % $rooms->count());
                $room = $rooms[$roomIndex];
                
                foreach ($timeSlots as $slot) {
                    $startTime = $currentDate->copy()->setTime($slot['start'], 0);
                    $endTime = $startTime->copy()->addMinutes($movieDuration);
                    
                    // Skip if start time is in the past
                    if ($startTime->isPast()) {
                        continue;
                    }
                    
                    // Check for conflicts
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
        
        $this->info("\n✅ Successfully created {$createdCount} showtimes!");
        return 0;
    }
}

