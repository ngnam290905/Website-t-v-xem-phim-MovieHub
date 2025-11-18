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

class SimpleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo vai trò nếu chưa có
        $adminRole = VaiTro::firstOrCreate(
            ['ten' => 'Admin'],
            ['mo_ta' => 'Quản trị viên hệ thống']
        );

        $customerRole = VaiTro::firstOrCreate(
            ['ten' => 'Customer'],
            ['mo_ta' => 'Khách hàng']
        );

        // Tạo admin nếu chưa có
        $admin = NguoiDung::firstOrCreate(
            ['email' => 'admin@moviehub.com'],
            [
                'ho_ten' => 'Admin User',
                'mat_khau' => bcrypt('password'),
                'sdt' => '0123456789',
                'id_vai_tro' => $adminRole->id,
                'trang_thai' => 1
            ]
        );

        // Tạo khách hàng mẫu
        $customers = [];
        for ($i = 1; $i <= 5; $i++) {
            $customers[] = NguoiDung::firstOrCreate(
                ['email' => "customer$i@example.com"],
                [
                    'ho_ten' => "Customer $i",
                    'mat_khau' => bcrypt('password'),
                    'sdt' => '012345678' . $i,
                    'id_vai_tro' => $customerRole->id,
                    'trang_thai' => 1
                ]
            );
        }

        // Tạo phim mẫu
        $movies = [];
        $movieTitles = ['Avengers: Endgame', 'Spider-Man: No Way Home', 'Black Widow'];
        
        foreach ($movieTitles as $index => $title) {
            $movies[] = Phim::firstOrCreate(
                ['ten_phim' => $title],
                [
                    'do_dai' => 120 + ($index * 10),
                    'poster' => "poster_$index.jpg",
                    'mo_ta' => "Mô tả phim $title",
                    'dao_dien' => "Director $index",
                    'dien_vien' => "Actor 1, Actor 2",
                    'trailer' => "trailer_$index.mp4",
                    'trang_thai' => 1
                ]
            );
        }

        // Tạo phòng chiếu
        $room = PhongChieu::firstOrCreate(
            ['ten_phong' => 'Phòng 1'],
            [
                'so_hang' => 5,
                'so_cot' => 8,
                'suc_chua' => 40,
                'mo_ta' => 'Phòng chiếu 1',
                'trang_thai' => 1
            ]
        );

        // Tạo loại ghế
        $loaiGhe = LoaiGhe::firstOrCreate(
            ['ten_loai' => 'Thường'],
            ['he_so_gia' => 1.0]
        );

        // Tạo ghế
        for ($row = 1; $row <= 5; $row++) {
            for ($col = 1; $col <= 8; $col++) {
                Ghe::firstOrCreate(
                    [
                        'id_phong' => $room->id,
                        'so_ghe' => $row . chr(64 + $col)
                    ],
                    [
                        'so_hang' => $row,
                        'so_cot' => $col,
                        'id_loai' => $loaiGhe->id,
                        'trang_thai' => 1
                    ]
                );
            }
        }

        // Tạo suất chiếu
        $showtimes = [];
        foreach ($movies as $movie) {
            for ($day = 0; $day < 3; $day++) {
                $startTime = Carbon::now()->addDays($day)->setHour(14)->setMinute(0);
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

        // Tạo đặt vé và chi tiết
        $seats = Ghe::where('id_phong', $room->id)->get();
        $basePrice = 50000;

        foreach ($showtimes as $showtime) {
            // Tạo 3-8 đặt vé cho mỗi suất chiếu
            $numBookings = rand(3, 8);
            
            for ($i = 0; $i < $numBookings; $i++) {
                $customer = $customers[array_rand($customers)];
                $numSeats = rand(1, 3);
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

        echo "Dữ liệu mẫu đã được tạo thành công!\n";
    }
}
