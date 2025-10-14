<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VaiTro;
use App\Models\NguoiDung;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = VaiTro::firstOrCreate(['ten' => 'admin'], ['mo_ta' => 'Quản trị']);
        $staff = VaiTro::firstOrCreate(['ten' => 'staff'], ['mo_ta' => 'Nhân viên']);
        $user = VaiTro::firstOrCreate(['ten' => 'user'], ['mo_ta' => 'Người dùng']);

        NguoiDung::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'ho_ten' => 'Admin',
                'mat_khau' => bcrypt('password'),
                'id_vai_tro' => $admin->id,
                'trang_thai' => 1,
            ]
        );

        NguoiDung::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'ho_ten' => 'Staff',
                'mat_khau' => bcrypt('password'),
                'id_vai_tro' => $staff->id,
                'trang_thai' => 1,
            ]
        );
    }
}