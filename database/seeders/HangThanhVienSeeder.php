<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HangThanhVienSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('hang_thanh_vien')->insert([
            [
                'id_nguoi_dung' => 1,
                'id_tier' => 1,
                'uu_dai' => '0%',
                'diem_toi_thieu' => 0,
                'ngay_cap_nhat_hang' => now(),
            ],
            [
                'id_nguoi_dung' => 2,
                'id_tier' => 2,
                'uu_dai' => '5%',
                'diem_toi_thieu' => 500,
                'ngay_cap_nhat_hang' => now(),
            ]
        ]);
    }
}
