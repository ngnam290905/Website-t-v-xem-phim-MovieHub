<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SuatChieu;
use App\Models\Movie;
use App\Models\PhongChieu;
use Carbon\Carbon;

class SuatChieuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movies = Movie::all();
        $phongChieu = PhongChieu::all();
        
        $suatChieu = [];
        
        // Tạo suất chiếu cho mỗi phim trong 7 ngày tới
        foreach ($movies as $movie) {
            for ($day = 0; $day < 7; $day++) {
                $date = Carbon::now()->addDays($day);
                
                // Tạo 3-4 suất chiếu mỗi ngày cho mỗi phim
                $times = ['14:00', '16:30', '19:00', '21:30'];
                
                foreach ($times as $time) {
                    $phong = $phongChieu->random();
                    $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $time);
                    $endTime = $startTime->copy()->addMinutes($movie->do_dai + 15); // Thêm 15 phút nghỉ
                    
                    $suatChieu[] = [
                        'id_phim' => $movie->id,
                        'id_phong' => $phong->id,
                        'thoi_gian_bat_dau' => $startTime,
                        'thoi_gian_ket_thuc' => $endTime,
                        'trang_thai' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }
        }
        
        // Chèn dữ liệu theo batch để tối ưu hiệu suất
        SuatChieu::insert($suatChieu);
    }
}
