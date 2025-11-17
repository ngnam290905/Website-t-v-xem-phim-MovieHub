<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DatVe;

class DatVeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nguoiDungIds = \App\Models\NguoiDung::pluck('id')->toArray();
        $suatChieuIds = \App\Models\SuatChieu::pluck('id')->toArray();
        $khuyenMaiIds = \App\Models\KhuyenMai::pluck('id')->toArray();
        $gheIds = \App\Models\Ghe::pluck('id')->toArray();
        $comboIds = \App\Models\Combo::pluck('id')->toArray();

        for ($i = 0; $i < 20; $i++) {
            $datVe = DatVe::create([
                'id_nguoi_dung' => $nguoiDungIds[$i % count($nguoiDungIds)],
                'id_suat_chieu' => $suatChieuIds[$i % count($suatChieuIds)],
                'id_khuyen_mai' => $khuyenMaiIds ? $khuyenMaiIds[$i % count($khuyenMaiIds)] : null,
                'tong_tien_goc' => 120000 + $i * 5000,
                'tien_giam_khuyen_mai' => ($i % 2 == 0) ? 20000 : 0,
                'tien_giam_thanh_vien' => ($i % 3 == 0) ? 15000 : 0,
                'diem_su_dung' => ($i % 4 == 0) ? 100 : 0,
                'tien_giam_diem' => ($i % 4 == 0) ? 10000 : 0,
                'diem_tich_luy' => 10 + $i,
                'tong_tien' => 120000 + $i * 5000 - (($i % 2 == 0) ? 20000 : 0) - (($i % 3 == 0) ? 15000 : 0) - (($i % 4 == 0) ? 10000 : 0),
                'trang_thai' => ($i % 2 == 0) ? 1 : 0,
            ]);

            // Tạo chi tiết đặt vé (giả lập 2 ghế cho mỗi vé)
            for ($j = 0; $j < 2; $j++) {
                \App\Models\ChiTietDatVe::create([
                    'id_dat_ve' => $datVe->id,
                    'id_ghe' => $gheIds[($i*2 + $j) % count($gheIds)],
                    'gia' => 60000,
                    'gia_ve' => 60000,
                ]);
            }

            // Tạo chi tiết combo (giả lập 1 combo cho mỗi vé)
            if ($comboIds) {
                \App\Models\ChiTietCombo::create([
                    'id_dat_ve' => $datVe->id,
                    'id_combo' => $comboIds[$i % count($comboIds)],
                    'so_luong' => 1,
                    'gia_ap_dung' => 50000,
                ]);
            }
        }
    }
}
