<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DatVe;
use App\Models\NguoiDung;
use App\Models\SuatChieu;
use App\Models\ChiTietDatVe;
use App\Models\Ghe;
use App\Models\ChiTietCombo;
use App\Models\Combo;
use App\Models\ThanhToan;
use App\Models\Phim;
use App\Models\PhongChieu;
use App\Models\LoaiGhe;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DatVeSeeder extends Seeder
{
    public function run()
    {
        // Sử dụng user có sẵn (ID=1: Admin)
        $user = NguoiDung::find(1);
        if (!$user) {
            $user = NguoiDung::create([
                'ho_ten' => 'Test User',
                'email' => 'test@example.com',
                'mat_khau' => Hash::make('password'),
                'trang_thai' => 1,
            ]);
        }

        // Tạo loai_ghe nếu chưa có (cần cho ghe)
        $loaiGheThuong = LoaiGhe::find(1);
        if (!$loaiGheThuong) {
            LoaiGhe::create([
                'ten_loai' => 'Thường',
                'he_so_gia' => 1.00,
            ]);
        }

        // Tạo phim nếu chưa có (chỉ dùng trường tồn tại)
        $phim = Phim::find(1);
        if (!$phim) {
            $phim = Phim::create([
                'ten_phim' => 'Phim Test Hành Động',
                'the_loai' => 'Phim hành động',
                'do_dai' => 120,
                'poster' => 'poster_test.jpg',
                'mo_ta' => 'Mô tả phim test.',
                'dao_dien' => 'Đạo diễn Test',
                'dien_vien' => 'Diễn viên Test',
                'trailer' => 'trailer_test.mp4',
                'trang_thai' => 1,
            ]);
        }

        // Tạo phòng chiếu nếu chưa có (chỉ dùng trường tồn tại: suc_chua thay so_ghe)
        $phong = PhongChieu::find(1);
        if (!$phong) {
            $phong = PhongChieu::create([
                'ten_phong' => 'Phòng 1',
                'so_hang' => 10,
                'so_cot' => 10,
                'suc_chua' => 100,  // Sức chứa thay cho so_ghe
                'mo_ta' => 'Phòng chiếu tiêu chuẩn.',
                'trang_thai' => 1,
            ]);
        }

        // Tạo suất chiếu nếu chưa có (thêm thoi_gian_ket_thuc = bat_dau + 2h)
        $suatChieu = SuatChieu::find(1);
        if (!$suatChieu) {
            $thoiGianBatDau = Carbon::now()->addHours(2);
            SuatChieu::create([
                'id_phim' => $phim->id,
                'id_phong' => $phong->id,
                'thoi_gian_bat_dau' => $thoiGianBatDau,
                'thoi_gian_ket_thuc' => $thoiGianBatDau->copy()->addHours(2),
                'trang_thai' => 1,
            ]);
            $suatChieu = SuatChieu::find(1);
        }

        // Tạo vài ghế nếu chưa có (5 ghế, dùng loai ID=1)
        $gheIds = [];
        for ($j = 1; $j <= 5; $j++) {
            $ghe = Ghe::create([
                'id_phong' => $phong->id,
                'so_ghe' => (string) $j,  // Varchar
                'so_hang' => 1,
                'id_loai' => 1,  // Loại thường
                'trang_thai' => 1,  // Trống
            ]);
            $gheIds[] = $ghe->id;
        }

        // Tạo combo nếu chưa có
        $combo = Combo::find(1);
        if (!$combo) {
            Combo::create([
                'ten' => 'Popcorn + Nước',
                'mo_ta' => 'Combo cơ bản.',
                'gia' => 50000.00,
                'trang_thai' => 1,
            ]);
            $combo = Combo::find(1);
        }

        // Tạo 5 booking mẫu (dat_ve không có tong_tien, nên bỏ phần cập nhật)
        for ($i = 1; $i <= 5; $i++) {
            $booking = DatVe::create([
                'id_nguoi_dung' => $user->id,
                'id_suat_chieu' => $suatChieu->id,
                'trang_thai' => rand(0, 2),  // 0: chờ, 1: xác nhận, 2: hủy
                // Không có tong_tien, bỏ
            ]);

            // Tạo thanh toán mẫu
            ThanhToan::create([
                'id_dat_ve' => $booking->id,
                'phuong_thuc' => 'Chuyển khoản',
                'so_tien' => 200000.00 + ($i * 50000.00),
                'ma_giao_dich' => 'TXN' . $booking->id . $i,
                'trang_thai' => 1,
                'thoi_gian' => Carbon::now()->subDays($i),
            ]);

            // Chi tiết ghế: Chọn random 2-3 ghế
            $numGhe = rand(2, 3);
            $selectedGheIds = array_rand($gheIds, $numGhe);
            if (!is_array($selectedGheIds)) $selectedGheIds = [$selectedGheIds];
            foreach ($selectedGheIds as $gheId) {
                ChiTietDatVe::create([
                    'id_dat_ve' => $booking->id,
                    'id_ghe' => $gheIds[$gheId],
                    'gia' => 100000.00,
                ]);
                // Cập nhật trạng thái ghế
                Ghe::find($gheIds[$gheId])->update(['trang_thai' => 0]);
            }

            // Chi tiết combo: Thêm ngẫu nhiên (dùng gia_khuyen_mai = gia nếu không khuyến mãi)
            if (rand(0, 1)) {
                ChiTietCombo::create([
                    'id_dat_ve' => $booking->id,
                    'id_combo' => $combo->id,
                    'so_luong' => 1,
                    'gia_khuyen_mai' => $combo->gia,  // Không khuyến mãi
                ]);
            }
        }
    }
}
