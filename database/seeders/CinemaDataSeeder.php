<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PhongChieu;
use App\Models\Ghe;
use App\Models\SuatChieu;
use App\Models\Phim;
use App\Models\LoaiGhe;
use Carbon\Carbon;

class CinemaDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample rooms (legacy columns via model mutators)
        $rooms = [
            ['ten_phong' => 'Phòng 1 - Standard', 'so_hang' => 8,  'so_cot' => 12, 'suc_chua' => 96,  'mo_ta' => 'Phòng chiếu tiêu chuẩn với 96 ghế',  'trang_thai' => 1],
            ['ten_phong' => 'Phòng 2 - VIP',      'so_hang' => 6,  'so_cot' => 8,  'suc_chua' => 48,  'mo_ta' => 'Phòng chiếu VIP với 48 ghế cao cấp', 'trang_thai' => 1],
            ['ten_phong' => 'Phòng 3 - IMAX',     'so_hang' => 10, 'so_cot' => 15, 'suc_chua' => 150, 'mo_ta' => 'Phòng chiếu IMAX với 150 ghế',        'trang_thai' => 1],
            ['ten_phong' => 'Phòng 4 - 3D',       'so_hang' => 7,  'so_cot' => 10, 'suc_chua' => 70,  'mo_ta' => 'Phòng chiếu 3D với 70 ghế',          'trang_thai' => 1],
        ];

        foreach ($rooms as $data) {
            $room = PhongChieu::create($data);
            $this->createSeatsForRoom($room);
        }

        // Create sample showtimes
        $this->createSampleShowtimes();
    }

    private function createSeatsForRoom(PhongChieu $room)
    {
        $rows = $room->rows;
        $cols = $room->cols;

        // Get default seat type (Ghế thường) or first available
        $seatType = LoaiGhe::where('ten_loai', 'Ghế thường')->first() ?: LoaiGhe::first();
        $seatTypeId = $seatType ? $seatType->id : null;
        
        for ($row = 1; $row <= $rows; $row++) {
            for ($col = 1; $col <= $cols; $col++) {
                $seatCode = chr(64 + $row) . $col; // A1, A2, ...
                Ghe::create([
                    'id_phong' => $room->id,
                    'id_loai' => $seatTypeId,
                    'so_hang' => $row,
                    'so_ghe' => $seatCode,
                    'trang_thai' => 1,
                ]);
            }
        }
    }

    private function createSampleShowtimes()
    {
        $movies = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])->take(3)->get();
        $rooms = PhongChieu::where('trang_thai', 1)->get();
        
        if ($movies->isEmpty() || $rooms->isEmpty()) {
            return;
        }
        
        $today = Carbon::today();
        
        foreach ($movies as $movie) {
            foreach ($rooms as $room) {
                // Create 3 showtimes per day for 7 days
                for ($day = 0; $day < 7; $day++) {
                    $date = $today->copy()->addDays($day);
                    
                    // Morning showtime
                    $this->createShowtime($movie, $room, $date->copy()->setTime(9, 0));
                    
                    // Afternoon showtime
                    $this->createShowtime($movie, $room, $date->copy()->setTime(14, 30));
                    
                    // Evening showtime
                    $this->createShowtime($movie, $room, $date->copy()->setTime(19, 0));
                }
            }
        }
    }

    private function createShowtime($movie, $room, $startTime)
    {
        // Assume movie duration is 120 minutes
        $endTime = $startTime->copy()->addMinutes(120);
        
        // Determine status based on current time
        $now = Carbon::now();
        $status = 'coming';
        
        if ($now->between($startTime, $endTime)) {
            $status = 'ongoing';
        } elseif ($now->gt($endTime)) {
            $status = 'finished';
        }
        
        SuatChieu::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $status,
        ]);
    }
}