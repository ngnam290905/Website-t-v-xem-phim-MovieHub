<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PhongChieu;

class PhongChieuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $phongChieu = [
            [
                'ten_phong' => 'Phòng 1 - IMAX',
                'so_hang' => 12,
                'so_cot' => 20,
                'suc_chua' => 240,
                'mo_ta' => 'Phòng chiếu IMAX với màn hình lớn và âm thanh vòm 7.1',
                'trang_thai' => true
            ],
            [
                'ten_phong' => 'Phòng 2 - 3D',
                'so_hang' => 10,
                'so_cot' => 18,
                'suc_chua' => 180,
                'mo_ta' => 'Phòng chiếu 3D với công nghệ RealD',
                'trang_thai' => true
            ],
            [
                'ten_phong' => 'Phòng 3 - 2D',
                'so_hang' => 8,
                'so_cot' => 15,
                'suc_chua' => 120,
                'mo_ta' => 'Phòng chiếu 2D tiêu chuẩn',
                'trang_thai' => true
            ],
            [
                'ten_phong' => 'Phòng 4 - VIP',
                'so_hang' => 6,
                'so_cot' => 12,
                'suc_chua' => 72,
                'mo_ta' => 'Phòng chiếu VIP với ghế massage và dịch vụ cao cấp',
                'trang_thai' => true
            ],
            [
                'ten_phong' => 'Phòng 5 - 4DX',
                'so_hang' => 8,
                'so_cot' => 16,
                'suc_chua' => 128,
                'mo_ta' => 'Phòng chiếu 4DX với ghế chuyển động và hiệu ứng đặc biệt',
                'trang_thai' => true
            ]
        ];

        foreach ($phongChieu as $phong) {
            PhongChieu::create($phong);
        }
    }
}
