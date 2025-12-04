<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Phim;
use App\Models\PhongChieu;
use App\Models\SuatChieu;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddShowtimesForAllMovies extends Command
{
    protected $signature = 'showtimes:add-all {--days=7 : Number of days to create showtimes for} {--skip-conflict : Skip conflict check}';
    protected $description = 'Add showtimes for ALL movies';

    public function handle()
    {
        $days = (int) $this->option('days');
        $skipConflict = $this->option('skip-conflict');
        
        // Lấy tất cả phim (có thể lọc theo trạng thái nếu cần)
        $movies = Phim::all();
        
        if ($movies->isEmpty()) {
            $this->error('Không tìm thấy phim nào.');
            return 1;
        }
        
        $rooms = PhongChieu::where('trang_thai', 1)->get();
        
        if ($rooms->isEmpty()) {
            $this->error('Không tìm thấy phòng chiếu nào.');
            return 1;
        }
        
        $this->info("Tìm thấy {$movies->count()} phim và {$rooms->count()} phòng chiếu");
        $this->info("Đang tạo suất chiếu cho {$days} ngày tới...");
        
        $now = Carbon::now();
        $createdCount = 0;
        $skippedCount = 0;
        
        // Các khung giờ chiếu
        $timeSlots = [
            '09:00',
            '10:00',
            '11:30',
            '13:00',
            '14:30',
            '16:00',
            '17:30',
            '19:00',
            '20:30',
            '22:00',
            '23:30'
        ];
        
        $bar = $this->output->createProgressBar($movies->count() * $days * count($timeSlots) * $rooms->count());
        $bar->start();
        
        foreach ($movies as $movie) {
            // Lấy độ dài phim, mặc định 120 phút nếu không có
            $movieDuration = $movie->do_dai ?? 120;
            
            for ($day = 0; $day <= $days; $day++) {
                $currentDate = $now->copy()->addDays($day)->startOfDay();
                
                foreach ($timeSlots as $time) {
                    $startTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $time);
                    
                    // Bỏ qua nếu thời gian đã qua
                    if ($startTime->isPast() && $day === 0) {
                        $bar->advance($rooms->count());
                        continue;
                    }
                    
                    $endTime = $startTime->copy()->addMinutes($movieDuration + 15); // +15 phút cho quảng cáo và dọn dẹp
                    
                    foreach ($rooms as $room) {
                        try {
                            // Kiểm tra xem suất chiếu đã tồn tại chưa
                            $exists = SuatChieu::where('id_phim', $movie->id)
                                ->where('id_phong', $room->id)
                                ->where('thoi_gian_bat_dau', $startTime)
                                ->exists();
                            
                            if ($exists) {
                                $skippedCount++;
                                $bar->advance();
                                continue;
                            }
                            
                            // Kiểm tra xung đột thời gian nếu không skip
                            if (!$skipConflict) {
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
                                
                                if ($conflict) {
                                    $skippedCount++;
                                    $bar->advance();
                                    continue;
                                }
                            }
                            
                            // Tạo suất chiếu
                            SuatChieu::create([
                                'id_phim' => $movie->id,
                                'id_phong' => $room->id,
                                'thoi_gian_bat_dau' => $startTime,
                                'thoi_gian_ket_thuc' => $endTime,
                                'trang_thai' => 1
                            ]);
                            
                            $createdCount++;
                        } catch (\Exception $e) {
                            $this->warn("\n⚠ Lỗi khi tạo suất chiếu: " . $e->getMessage());
                        }
                        
                        $bar->advance();
                    }
                }
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Đã tạo {$createdCount} suất chiếu mới!");
        if ($skippedCount > 0) {
            $this->info("⏭ Đã bỏ qua {$skippedCount} suất chiếu (trùng lặp hoặc xung đột)");
        }
        
        return 0;
    }
}

