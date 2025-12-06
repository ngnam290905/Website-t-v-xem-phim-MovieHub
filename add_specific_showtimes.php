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

echo "Adding specific showtimes with more time slots...\n\n";

$now = Carbon::now();
$createdCount = 0;

foreach ($movies as $movie) {
    $movieDuration = $movie->do_dai ?? 120;
    
    for ($day = 0; $day < 7; $day++) {
        $currentDate = $now->copy()->addDays($day)->startOfDay();
        
        foreach ($rooms as $room) {
            $startHour = ($day === 0) ? max(16, $now->hour + 1) : 8;
            $endHour = 22;
            
            for ($hour = $startHour; $hour <= $endHour; $hour++) {
                for ($minute = 0; $minute < 60; $minute += 30) {
                    if ($hour === 22 && $minute > 0) break;
                    
                    $startTime = $currentDate->copy()->setTime($hour, $minute);
                    $endTime = $startTime->copy()->addMinutes($movieDuration);
                    
                    if ($startTime->isPast()) {
                        continue;
                    }
                    
                    if ($endTime->hour >= 24 || ($endTime->hour === 23 && $endTime->minute > 0)) {
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
                        
                        if ($createdCount >= 50) {
                            break 3;
                        }
                    }
                }
                if ($createdCount >= 50) break;
            }
            if ($createdCount >= 50) break;
        }
        if ($createdCount >= 50) break;
    }
}

echo "\n✅ Successfully created {$createdCount} additional showtimes!\n";

$totalToday = SuatChieu::whereDate('thoi_gian_bat_dau', today())
    ->where('thoi_gian_ket_thuc', '>', now())
    ->count();
$totalTomorrow = SuatChieu::whereDate('thoi_gian_bat_dau', today()->addDay())
    ->count();
$totalAll = SuatChieu::where('thoi_gian_ket_thuc', '>', now())->count();

echo "\nSummary:\n";
echo "Showtimes today (not ended): {$totalToday}\n";
echo "Showtimes tomorrow: {$totalTomorrow}\n";
echo "Total active showtimes: {$totalAll}\n";

