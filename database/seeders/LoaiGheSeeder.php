<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoaiGhe;

class LoaiGheSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loaiGhe = [
            [
                'ten_loai' => 'Ghế Thường',
                'he_so_gia' => 1.0
            ],
            [
                'ten_loai' => 'Ghế VIP',
                'he_so_gia' => 1.5
            ],
            [
                'ten_loai' => 'Ghế Đôi',
                'he_so_gia' => 2.0
            ],
            [
                'ten_loai' => 'Ghế Premium',
                'he_so_gia' => 1.8
            ]
        ];

        foreach ($loaiGhe as $loai) {
            LoaiGhe::create($loai);
        }
    }
}
