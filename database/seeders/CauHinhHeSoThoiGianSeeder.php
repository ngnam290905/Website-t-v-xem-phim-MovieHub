<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CauHinhHeSoThoiGianSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cau_hinh_he_so_thoi_gian')->insert([
            ['ten_quy_tac' => 'Đầu tuần',      'loai' => 'ngay_tuan', 'gia_tri' => '1-5', 'he_so' => 1.00, 'trang_thai' => true],
            ['ten_quy_tac' => 'Cuối tuần',     'loai' => 'ngay_tuan', 'gia_tri' => '6-0', 'he_so' => 1.30, 'trang_thai' => true],
            ['ten_quy_tac' => 'Đêm muộn',      'loai' => 'gio_chieu', 'gia_tri' => '22:00-06:00', 'he_so' => 0.80, 'trang_thai' => true],
            ['ten_quy_tac' => 'Sáng sớm',      'loai' => 'gio_chieu', 'gia_tri' => '06:00-11:00', 'he_so' => 0.90, 'trang_thai' => true],
        ]);
    }
}