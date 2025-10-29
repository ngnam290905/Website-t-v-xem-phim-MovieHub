<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PhongChieu;
use App\Models\Ghe;
use App\Models\SuatChieu;
use App\Models\Movie;
use Carbon\Carbon;

class CinemaDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample rooms
        $rooms = [
            [
                'name' => 'Phòng 1 - Standard',
                'rows' => 8,
                'cols' => 12,
                'suc_chua' => 96,
                'description' => 'Phòng chiếu tiêu chuẩn với 96 ghế',
                'type' => 'normal',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng 2 - VIP',
                'rows' => 6,
                'cols' => 8,
                'suc_chua' => 48,
                'description' => 'Phòng chiếu VIP với 48 ghế cao cấp',
                'type' => 'vip',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng 3 - IMAX',
                'rows' => 10,
                'cols' => 15,
                'suc_chua' => 150,
                'description' => 'Phòng chiếu IMAX với 150 ghế',
                'type' => 'imax',
                'status' => 'active',
            ],
            [
                'name' => 'Phòng 4 - 3D',
                'rows' => 7,
                'cols' => 10,
                'suc_chua' => 70,
                'description' => 'Phòng chiếu 3D với 70 ghế',
                'type' => '3d',
                'status' => 'active',
            ],
        ];

        foreach ($rooms as $roomData) {
            $room = PhongChieu::create($roomData);
            
            // Create seats for this room
            $this->createSeatsForRoom($room);
        }

        // Create sample showtimes
        $this->createSampleShowtimes();
    }

    private function createSeatsForRoom(PhongChieu $room)
    {
        $rows = $room->rows;
        $cols = $room->cols;
        
        for ($row = 1; $row <= $rows; $row++) {
            for ($col = 1; $col <= $cols; $col++) {
                $rowLabel = chr(64 + $row); // A, B, C, etc.
                $seatCode = $rowLabel . $col;
                
                // Determine seat type based on room type and position
                $seatType = $this->determineSeatType($room->type, $row, $rows);
                $price = $this->calculateSeatPrice($room->type, $seatType, $row, $rows);
                
                Ghe::create([
                    'room_id' => $room->id,
                    'seat_code' => $seatCode,
                    'row_label' => $rowLabel,
                    'col_number' => $col,
                    'type' => $seatType,
                    'status' => 'available',
                    'price' => $price,
                ]);
            }
        }
    }

    private function determineSeatType($roomType, $row, $totalRows)
    {
        // VIP seats in VIP rooms
        if ($roomType === 'vip') {
            return 'vip';
        }
        
        // Front rows are normal, back rows might be VIP
        if ($row <= 2) {
            return 'normal';
        } elseif ($row >= $totalRows - 1) {
            return 'vip';
        }
        
        return 'normal';
    }

    private function calculateSeatPrice($roomType, $seatType, $row, $totalRows)
    {
        $basePrice = 50000; // 50,000 VND base price
        
        // Room type multipliers
        $roomMultipliers = [
            'normal' => 1.0,
            'vip' => 1.5,
            '3d' => 1.3,
            'imax' => 1.8,
        ];
        
        // Seat type multipliers
        $seatMultipliers = [
            'normal' => 1.0,
            'vip' => 1.5,
            'disabled' => 0,
        ];
        
        // Row position multipliers (front rows more expensive)
        $rowMultiplier = 1.0;
        if ($row <= 2) {
            $rowMultiplier = 1.2; // Front rows
        } elseif ($row >= $totalRows - 1) {
            $rowMultiplier = 1.1; // Back rows
        }
        
        $price = $basePrice * 
                 ($roomMultipliers[$roomType] ?? 1.0) * 
                 ($seatMultipliers[$seatType] ?? 1.0) * 
                 $rowMultiplier;
        
        return round($price);
    }

    private function createSampleShowtimes()
    {
        $movies = Movie::where('trang_thai', 1)->take(3)->get();
        $rooms = PhongChieu::where('status', 'active')->get();
        
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