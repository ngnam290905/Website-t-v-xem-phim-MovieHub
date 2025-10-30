<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PhongChieu;
use App\Models\Ghe;
use App\Models\LoaiGhe;

class PhongChieuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $phongChieu = [
            [
                'name' => 'Phòng 1 - IMAX',
                'rows' => 12,
                'cols' => 20,
                'type' => 'IMAX',
                'description' => 'Phòng chiếu IMAX với màn hình lớn và âm thanh vòm 7.1',
                'status' => 'active',
                'audio_system' => 'Dolby Atmos',
                'screen_type' => 'IMAX Laser'
            ],
            [
                'name' => 'Phòng 2 - 3D',
                'rows' => 10,
                'cols' => 18,
                'type' => '3D',
                'description' => 'Phòng chiếu 3D với công nghệ RealD',
                'status' => 'active',
                'audio_system' => 'Dolby Digital 7.1',
                'screen_type' => 'RealD 3D'
            ],
            [
                'name' => 'Phòng 3 - 2D',
                'rows' => 8,
                'cols' => 15,
                'type' => '2D',
                'description' => 'Phòng chiếu 2D tiêu chuẩn',
                'status' => 'active',
                'audio_system' => 'Dolby Digital 5.1',
                'screen_type' => 'LED'
            ],
            [
                'name' => 'Phòng 4 - VIP',
                'rows' => 6,
                'cols' => 12,
                'type' => 'VIP',
                'description' => 'Phòng chiếu VIP với ghế massage và dịch vụ cao cấp',
                'status' => 'active',
                'audio_system' => 'Dolby Atmos',
                'screen_type' => '4K Laser'
            ],
            [
                'name' => 'Phòng 5 - 4DX',
                'rows' => 8,
                'cols' => 16,
                'type' => '4DX',
                'description' => 'Phòng chiếu 4DX với ghế chuyển động và hiệu ứng đặc biệt',
                'status' => 'active',
                'audio_system' => 'Dolby Atmos + 4DX Sound',
                'screen_type' => '4DX Screen'
            ]
        ];

        foreach ($phongChieu as $phongData) {
            $phong = PhongChieu::create($phongData);
            
            // Tạo ghế cho phòng
            $this->createSeatsForRoom($phong);
        }
    }

    /**
     * Create seats for a room
     */
    private function createSeatsForRoom(PhongChieu $phong)
    {
        $normalSeatType = LoaiGhe::where('ten_loai', 'Ghế thường')->first();
        $vipSeatType = LoaiGhe::where('ten_loai', 'Ghế VIP')->first();
        
        if (!$normalSeatType) {
            $normalSeatType = LoaiGhe::first();
        }

        $seats = [];
        
        for ($row = 1; $row <= $phong->rows; $row++) {
            $rowLabel = chr(64 + $row); // A, B, C, ...
            
            for ($col = 1; $col <= $phong->cols; $col++) {
                // Phòng VIP có ghế VIP ở hàng cuối
                $isVip = ($phong->name === 'Phòng 4 - VIP' && $row === $phong->rows);
                $seatType = $isVip ? $vipSeatType : $normalSeatType;
                
                $seats[] = [
                    'room_id' => $phong->id,
                    'id_loai' => $seatType->id,
                    'seat_code' => $rowLabel . $col, // A1, A2, B1, B2, etc.
                    'row_label' => $rowLabel,
                    'col_number' => $col,
                    'so_ghe' => $col,
                    'status' => 'available',
                    'price' => $seatType->he_so_gia * 50000, // Base price 50k * coefficient
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        Ghe::insert($seats);
    }
}
