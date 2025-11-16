<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VaiTro;
use App\Models\NguoiDung;
use App\Models\Phim;
use App\Models\PhongChieu;
use App\Models\LoaiGhe;
use App\Models\Ghe;
use App\Models\SuatChieu;
use App\Models\DatVe;
use App\Models\ChiTietDatVe;
use Carbon\Carbon;

class CinemaDataSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo vai trò
        $adminRole = VaiTro::create([
            'ten' => 'Admin',
            'mo_ta' => 'Quản trị viên hệ thống'
        ]);

        $staffRole = VaiTro::create([
            'ten' => 'Staff',
            'mo_ta' => 'Nhân viên'
        ]);

        $customerRole = VaiTro::create([
            'ten' => 'Customer',
            'mo_ta' => 'Khách hàng'
        ]);

        // Tạo người dùng
        $admin = NguoiDung::create([
            'ho_ten' => 'Admin User',
            'email' => 'admin@moviehub.com',
            'mat_khau' => bcrypt('password'),
            'sdt' => '0123456789',
            'id_vai_tro' => $adminRole->id,
            'trang_thai' => 1
        ]);

        $customers = [];
        for ($i = 1; $i <= 10; $i++) {
            $customers[] = NguoiDung::create([
                'ho_ten' => "Customer $i",
                'email' => "customer$i@example.com",
                'mat_khau' => bcrypt('password'),
                'sdt' => '012345678' . $i,
                'id_vai_tro' => $customerRole->id,
                'trang_thai' => 1
            ]);
        }

        // Tạo phim
        $movies = [];
        $movieTitles = [
            'Avengers: Endgame',
            'Spider-Man: No Way Home',
            'Black Widow',
            'Shang-Chi',
            'Eternals',
            'Doctor Strange 2',
            'Thor: Love and Thunder',
            'Black Panther 2',
            'Ant-Man 3',
            'Guardians 3'
        ];

        foreach ($movieTitles as $index => $title) {
            $movies[] = Phim::create([
                'ten_phim' => $title,
                'do_dai' => 120 + ($index * 10),
                'poster' => "poster_$index.jpg",
                'mo_ta' => "Mô tả phim $title",
                'dao_dien' => "Director $index",
                'dien_vien' => "Actor 1, Actor 2, Actor 3",
                'trailer' => "trailer_$index.mp4",
                'trang_thai' => 1
            ]);
        }

        // Tạo phòng chiếu
        $rooms = [];
        for ($i = 1; $i <= 5; $i++) {
            $rooms[] = PhongChieu::create([
                'ten_phong' => "Phòng $i",
                'so_hang' => 10,
                'so_cot' => 15,
                'suc_chua' => 150,
                'mo_ta' => "Phòng chiếu $i",
                'trang_thai' => 1
            ]);
        }

        // Tạo loại ghế
        $loaiGhe = [];
        $loaiGhe[] = LoaiGhe::create(['ten_loai' => 'Thường', 'he_so_gia' => 1.0]);
        $loaiGhe[] = LoaiGhe::create(['ten_loai' => 'VIP', 'he_so_gia' => 1.5]);
        $loaiGhe[] = LoaiGhe::create(['ten_loai' => 'Couple', 'he_so_gia' => 2.0]);

        // Tạo ghế
        foreach ($rooms as $room) {
            for ($row = 1; $row <= $room->so_hang; $row++) {
                for ($col = 1; $col <= $room->so_cot; $col++) {
                    $seatType = $loaiGhe[array_rand($loaiGhe)];
                    Ghe::create([
                        'id_phong' => $room->id,
                        'so_ghe' => $row . chr(64 + $col),
                        'so_hang' => $row,
                        'id_loai' => $seatType->id,
                        'trang_thai' => 1
                    ]);
                }
            }
        }

        // Tạo suất chiếu
        $showtimes = [];
        $basePrice = 50000; // 50,000 VNĐ

        foreach ($movies as $movie) {
            foreach ($rooms as $room) {
                for ($day = 0; $day < 7; $day++) {
                    for ($time = 0; $time < 3; $time++) {
                        $startTime = Carbon::now()->addDays($day)->setHour(14 + $time * 3)->setMinute(0);
                        $endTime = $startTime->copy()->addMinutes($movie->do_dai);
                        
                        $showtimes[] = SuatChieu::create([
                            'id_phim' => $movie->id,
                            'id_phong' => $room->id,
                            'thoi_gian_bat_dau' => $startTime,
                            'thoi_gian_ket_thuc' => $endTime,
                            'trang_thai' => 1
                        ]);
                    }
                }
            }
        }

        // Tạo đặt vé và chi tiết
        foreach ($showtimes as $showtime) {
            $room = $showtime->phongChieu;
            $seats = Ghe::where('id_phong', $room->id)->get();
            
            // Tạo 5-15 đặt vé cho mỗi suất chiếu
            $numBookings = rand(5, 15);
            
            for ($i = 0; $i < $numBookings; $i++) {
                $customer = $customers[array_rand($customers)];
                $numSeats = rand(1, 4);
                $selectedSeats = $seats->random($numSeats);
                
                $booking = DatVe::create([
                    'id_nguoi_dung' => $customer->id,
                    'id_suat_chieu' => $showtime->id,
                    'trang_thai' => 1
                ]);
                
                foreach ($selectedSeats as $seat) {
                    $price = $basePrice * $seat->loaiGhe->he_so_gia;
                    
                    ChiTietDatVe::create([
                        'id_dat_ve' => $booking->id,
                        'id_ghe' => $seat->id,
                        'gia_ve' => $price
                    ]);
                }
            }
        }
    }
}