<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PhongChieu;
use App\Models\SuatChieu;
use App\Models\Phim;
use Carbon\Carbon;

class CinemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo phòng chiếu
        $phong1 = PhongChieu::create([
            'ten_phong' => 'Phòng 1 - IMAX',
            'so_hang' => 12,
            'so_cot' => 20,
            'suc_chua' => 240,
            'mo_ta' => 'Phòng chiếu IMAX với âm thanh và hình ảnh chất lượng cao',
            'trang_thai' => 1,
        ]);

        $phong2 = PhongChieu::create([
            'ten_phong' => 'Phòng 2 - VIP',
            'so_hang' => 8,
            'so_cot' => 15,
            'suc_chua' => 120,
            'mo_ta' => 'Phòng chiếu VIP với ghế ngồi thoải mái',
            'trang_thai' => 1,
        ]);

        $phong3 = PhongChieu::create([
            'ten_phong' => 'Phòng 3 - Standard',
            'so_hang' => 10,
            'so_cot' => 18,
            'suc_chua' => 180,
            'mo_ta' => 'Phòng chiếu tiêu chuẩn',
            'trang_thai' => 1,
        ]);

        $phong4 = PhongChieu::create([
            'ten_phong' => 'Phòng 4 - 3D',
            'so_hang' => 9,
            'so_cot' => 16,
            'suc_chua' => 144,
            'mo_ta' => 'Phòng chiếu 3D với công nghệ hiện đại',
            'trang_thai' => 1,
        ]);

        // Lấy một số phim để tạo suất chiếu
        $phims = Phim::take(5)->get();

        if ($phims->count() > 0) {
            // Tạo suất chiếu cho phim đầu tiên
            $phim1 = $phims->first();
            
            // Suất chiếu hôm nay
            SuatChieu::create([
                'id_phim' => $phim1->id,
                'id_phong' => $phong1->id,
                'thoi_gian_bat_dau' => Carbon::today()->setTime(14, 0),
                'thoi_gian_ket_thuc' => Carbon::today()->setTime(16, 30),
                'trang_thai' => 1,
            ]);

            SuatChieu::create([
                'id_phim' => $phim1->id,
                'id_phong' => $phong2->id,
                'thoi_gian_bat_dau' => Carbon::today()->setTime(19, 0),
                'thoi_gian_ket_thuc' => Carbon::today()->setTime(21, 30),
                'trang_thai' => 1,
            ]);

            // Suất chiếu ngày mai
            SuatChieu::create([
                'id_phim' => $phim1->id,
                'id_phong' => $phong1->id,
                'thoi_gian_bat_dau' => Carbon::tomorrow()->setTime(10, 0),
                'thoi_gian_ket_thuc' => Carbon::tomorrow()->setTime(12, 30),
                'trang_thai' => 1,
            ]);

            // Tạo suất chiếu cho phim thứ 2 nếu có
            if ($phims->count() > 1) {
                $phim2 = $phims->skip(1)->first();
                
                SuatChieu::create([
                    'id_phim' => $phim2->id,
                    'id_phong' => $phong3->id,
                    'thoi_gian_bat_dau' => Carbon::today()->setTime(16, 0),
                    'thoi_gian_ket_thuc' => Carbon::today()->setTime(18, 30),
                    'trang_thai' => 1,
                ]);

                SuatChieu::create([
                    'id_phim' => $phim2->id,
                    'id_phong' => $phong4->id,
                    'thoi_gian_bat_dau' => Carbon::tomorrow()->setTime(14, 0),
                    'thoi_gian_ket_thuc' => Carbon::tomorrow()->setTime(16, 30),
                    'trang_thai' => 1,
                ]);
            }
        }
    }
}
