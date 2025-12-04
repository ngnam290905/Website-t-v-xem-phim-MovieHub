<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Phim;
use App\Models\PhongChieu;
use App\Models\SuatChieu;
use Carbon\Carbon;

$movies = Phim::where('trang_thai', 'dang_chieu')->get();
$rooms = PhongChieu::where('trang_thai', 1)->get();

echo "Movies: " . $movies->count() . "\n";
echo "Rooms: " . $rooms->count() . "\n\n";

foreach ($movies as $movie) {
    $count = SuatChieu::where('id_phim', $movie->id)
        ->where('thoi_gian_ket_thuc', '>', now())
        ->count();
    echo $movie->ten_phim . ": " . $count . " showtimes\n";
}

echo "\nCreating additional showtimes...\n";

$now = Carbon::now();
$createdCount = 0;

foreach ($movies as $movie) {
    $movieDuration = $movie->do_dai ?? 120;
    
    for ($day = 0; $day < 7; $day++) {
        $currentDate = $now->copy()->addDays($day);
        
        $timeSlots = [
            ['start' => 9, 'end' => 11],
            ['start' => 12, 'end' => 14],
            ['start' => 15, 'end' => 17],
            ['start' => 18, 'end' => 20],
            ['start' => 21, 'end' => 23],
        ];
        
        if ($day === 0) {
            $startHour = max(16, $now->hour + 1);
            $timeSlots = [];
            for ($h = $startHour; $h < 23; $h += 3) {
                $timeSlots[] = ['start' => $h, 'end' => $h + 2];
            }
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
                    echo "Created: {$movie->ten_phim} - {$room->ten_phong} - {$startTime->format('d/m/Y H:i')}\n";
                }
            }
        }
    }
}

echo "\n✅ Successfully created {$createdCount} showtimes!\n";

