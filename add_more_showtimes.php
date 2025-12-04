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

echo "Adding more showtimes...\n\n";

$now = Carbon::now();
$createdCount = 0;

foreach ($movies as $movie) {
    $movieDuration = $movie->do_dai ?? 120;
    
    for ($day = 0; $day < 7; $day++) {
        $currentDate = $now->copy()->addDays($day);
        
        $timeSlots = [
            ['start' => 8, 'end' => 10],
            ['start' => 10, 'end' => 12],
            ['start' => 13, 'end' => 15],
            ['start' => 15, 'end' => 17],
            ['start' => 17, 'end' => 19],
            ['start' => 19, 'end' => 21],
            ['start' => 21, 'end' => 23],
        ];
        
        if ($day === 0) {
            $startHour = max(16, $now->hour + 1);
            $timeSlots = [];
            for ($h = $startHour; $h <= 22; $h += 2) {
                if ($h + 2 <= 23) {
                    $timeSlots[] = ['start' => $h, 'end' => $h + 2];
                }
            }
        }
        
        foreach ($rooms as $room) {
            foreach ($timeSlots as $slot) {
                $startTime = $currentDate->copy()->setTime($slot['start'], 0);
                $endTime = $startTime->copy()->addMinutes($movieDuration);
                
                if ($startTime->isPast()) {
                    continue;
                }
                
                if ($endTime->hour >= 24) {
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

echo "\n✅ Successfully created {$createdCount} additional showtimes!\n";

$totalToday = SuatChieu::whereDate('thoi_gian_bat_dau', today())
    ->where('thoi_gian_ket_thuc', '>', now())
    ->count();
$totalTomorrow = SuatChieu::whereDate('thoi_gian_bat_dau', today()->addDay())
    ->count();

echo "\nSummary:\n";
echo "Showtimes today (not ended): {$totalToday}\n";
echo "Showtimes tomorrow: {$totalTomorrow}\n";

