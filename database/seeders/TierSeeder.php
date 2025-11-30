<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'ten_hang' => 'Đồng',
                'mo_ta' => 'Hạng thành viên cơ bản dành cho người mới',
                'uu_dai' => 'Tích điểm khi mua vé và combo',
                'diem_toi_thieu' => 0,
                'diem_toi_da' => 999,
                'giam_gia_ve' => 0,
                'giam_gia_combo' => 0,
                'ty_le_tich_diem' => 1.0,
                'so_thu_tu' => 1,
                'mau_sac' => '#CD7F32',
                'icon' => 'bronze-medal.png',
                'trang_thai' => 1,
            ],
            [
                'ten_hang' => 'Bạc',
                'mo_ta' => 'Hạng thành viên bạc với nhiều ưu đãi hơn',
                'uu_dai' => 'Giảm 5% vé và combo, tích điểm nhanh hơn 20%',
                'diem_toi_thieu' => 1000,
                'diem_toi_da' => 4999,
                'giam_gia_ve' => 5.00,
                'giam_gia_combo' => 5.00,
                'ty_le_tich_diem' => 1.2,
                'so_thu_tu' => 2,
                'mau_sac' => '#C0C0C0',
                'icon' => 'silver-medal.png',
                'trang_thai' => 1,
            ],
            [
                'ten_hang' => 'Vàng',
                'mo_ta' => 'Hạng thành viên vàng với quyền lợi cao cấp',
                'uu_dai' => 'Giảm 10% vé và combo, tích điểm x1.5, ưu tiên đặt vé, nâng cấp ghế VIP',
                'diem_toi_thieu' => 5000,
                'diem_toi_da' => 9999,
                'giam_gia_ve' => 10.00,
                'giam_gia_combo' => 10.00,
                'ty_le_tich_diem' => 1.5,
                'so_thu_tu' => 3,
                'mau_sac' => '#FFD700',
                'icon' => 'gold-medal.png',
                'trang_thai' => 1,
            ],
            [
                'ten_hang' => 'Bạch Kim',
                'mo_ta' => 'Hạng thành viên cao nhất với đặc quyền VIP',
                'uu_dai' => 'Giảm 15% vé và combo, tích điểm x2, ưu tiên tối đa, VIP lounge, hỗ trợ 24/7',
                'diem_toi_thieu' => 10000,
                'diem_toi_da' => null, // Không giới hạn
                'giam_gia_ve' => 15.00,
                'giam_gia_combo' => 15.00,
                'ty_le_tich_diem' => 2.0,
                'so_thu_tu' => 4,
                'mau_sac' => '#E5E4E2',
                'icon' => 'platinum-medal.png',
                'trang_thai' => 1,
            ],
        ];

        foreach ($tiers as $tier) {
            DB::table('tier')->insert($tier);
        }
    }
}
