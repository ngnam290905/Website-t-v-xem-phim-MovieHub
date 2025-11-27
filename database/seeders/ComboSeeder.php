<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComboSeeder extends Seeder
{
    public function run(): void
    {
        $combos = [
            [
                'ten' => 'Combo Đôi',
                'mo_ta' => '1 Bắp lớn + 2 Nước ngọt 32oz - Combo tiết kiệm cho 2 người',
                'gia' => 85000,
                'gia_goc' => 100000,
                'anh' => null,
                'combo_noi_bat' => 1,
                'so_luong_toi_da' => 10,
                'yeu_cau_it_nhat_ve' => 2,
                'ngay_bat_dau' => null,
                'ngay_ket_thuc' => null,
                'trang_thai' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten' => 'Combo Gia Đình',
                'mo_ta' => '2 Bắp lớn + 4 Nước ngọt 32oz - Combo cho gia đình 4 người',
                'gia' => 150000,
                'gia_goc' => 180000,
                'anh' => null,
                'combo_noi_bat' => 1,
                'so_luong_toi_da' => 5,
                'yeu_cau_it_nhat_ve' => 4,
                'ngay_bat_dau' => null,
                'ngay_ket_thuc' => null,
                'trang_thai' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten' => 'Combo Solo',
                'mo_ta' => '1 Bắp vừa + 1 Nước ngọt 22oz - Combo cho 1 người',
                'gia' => 55000,
                'gia_goc' => 65000,
                'anh' => null,
                'combo_noi_bat' => 0,
                'so_luong_toi_da' => 20,
                'yeu_cau_it_nhat_ve' => 1,
                'ngay_bat_dau' => null,
                'ngay_ket_thuc' => null,
                'trang_thai' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten' => 'Combo VIP',
                'mo_ta' => '2 Bắp lớn + 2 Nước ngọt 32oz + 1 Snack mix - Combo cao cấp',
                'gia' => 120000,
                'gia_goc' => 150000,
                'anh' => null,
                'combo_noi_bat' => 1,
                'so_luong_toi_da' => 8,
                'yeu_cau_it_nhat_ve' => 2,
                'ngay_bat_dau' => null,
                'ngay_ket_thuc' => null,
                'trang_thai' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten' => 'Combo Nhóm',
                'mo_ta' => '3 Bắp lớn + 6 Nước ngọt 32oz - Combo cho nhóm 6 người',
                'gia' => 220000,
                'gia_goc' => 270000,
                'anh' => null,
                'combo_noi_bat' => 0,
                'so_luong_toi_da' => 3,
                'yeu_cau_it_nhat_ve' => 6,
                'ngay_bat_dau' => null,
                'ngay_ket_thuc' => null,
                'trang_thai' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($combos as $combo) {
            DB::table('combo')->insert($combo);
        }
    }
}

