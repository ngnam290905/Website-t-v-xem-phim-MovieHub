<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PhongChieu;
use App\Models\Ghe;
use App\Models\LoaiGhe;
use Illuminate\Support\Facades\DB;

class CreateStandardRoom extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'room:create-standard {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo phòng chiếu chuẩn với đầy đủ ghế VIP, ghế thường và ghế đôi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roomName = $this->argument('name') ?? 'Phòng Chuẩn ' . date('Y-m-d H:i');
        
        // Kiểm tra loại ghế
        $normalSeatType = LoaiGhe::where('ten_loai', 'Thường')
            ->orWhere('ten_loai', 'thường')
            ->orWhere('ten_loai', 'normal')
            ->orWhere('id', 1)
            ->first();
        
        $vipSeatType = LoaiGhe::where('ten_loai', 'VIP')
            ->orWhere('ten_loai', 'vip')
            ->orWhere('id', 2)
            ->first();
        
        $coupleSeatType = LoaiGhe::where('ten_loai', 'Đôi')
            ->orWhere('ten_loai', 'đôi')
            ->orWhere('ten_loai', 'couple')
            ->orWhere('id', 3)
            ->first();
        
        if (!$normalSeatType || !$vipSeatType || !$coupleSeatType) {
            $this->error('Thiếu loại ghế trong database! Vui lòng tạo các loại ghế: Thường, VIP, Đôi');
            return 1;
        }
        
        $this->info("Đang tạo phòng chiếu: {$roomName}");
        $this->info("Loại ghế:");
        $this->info("  - Thường: ID {$normalSeatType->id}");
        $this->info("  - VIP: ID {$vipSeatType->id}");
        $this->info("  - Đôi: ID {$coupleSeatType->id}");
        
        DB::beginTransaction();
        try {
            // Tạo phòng chiếu
            $phongChieu = PhongChieu::create([
                'ten_phong' => $roomName,
                'so_hang' => 12, // 12 hàng (A-L)
                'so_cot' => 18,  // 18 cột
                'suc_chua' => 0,  // Sẽ tính sau
                'trang_thai' => 1,
            ]);
            
            $this->info("✓ Đã tạo phòng chiếu ID: {$phongChieu->id}");
            
            // Tạo ghế theo layout chuẩn
            $seats = [];
            $totalSeats = 0;
            
            // Layout chuẩn:
            // - Hàng A-C (1-3): Ghế thường (18 ghế)
            // - Hàng D-F (4-6): Ghế VIP ở giữa (D3-D16, E3-E16, F3-F16), còn lại thường
            // - Hàng G-J (7-10): Ghế thường (18 ghế)
            // - Hàng K (11): Ghế thường (18 ghế)
            // - Hàng L (12): Ghế đôi (16 ghế đôi)
            
            for ($row = 1; $row <= 12; $row++) {
                $rowLetter = chr(64 + $row); // A, B, C, ..., L
                
                // Hàng L chỉ có 16 ghế đôi
                if ($rowLetter === 'L') {
                    $maxCols = 16;
                } else {
                    $maxCols = 18;
                }
                
                for ($col = 1; $col <= $maxCols; $col++) {
                    $seatCode = $rowLetter . $col;
                    
                    // Xác định loại ghế
                    $seatTypeId = $normalSeatType->id; // Mặc định ghế thường
                    
                    // Hàng L: tất cả là ghế đôi
                    if ($rowLetter === 'L') {
                        $seatTypeId = $coupleSeatType->id;
                    }
                    // Hàng D-F: ghế 3-16 là VIP
                    elseif (in_array($rowLetter, ['D', 'E', 'F']) && $col >= 3 && $col <= 16) {
                        $seatTypeId = $vipSeatType->id;
                    }
                    
                    $seats[] = [
                        'id_phong' => $phongChieu->id,
                        'id_loai' => $seatTypeId,
                        'so_hang' => $row,
                        'so_ghe' => $seatCode,
                        'trang_thai' => 1,
                    ];
                    $totalSeats++;
                }
            }
            
            // Chèn ghế vào database
            if (!empty($seats)) {
                // Chia nhỏ để tránh lỗi memory
                $chunks = array_chunk($seats, 100);
                foreach ($chunks as $chunk) {
                    Ghe::insert($chunk);
                }
            }
            
            // Cập nhật sức chứa
            $phongChieu->update(['suc_chua' => $totalSeats]);
            
            // Thống kê
            $normalCount = Ghe::where('id_phong', $phongChieu->id)
                ->where('id_loai', $normalSeatType->id)
                ->count();
            $vipCount = Ghe::where('id_phong', $phongChieu->id)
                ->where('id_loai', $vipSeatType->id)
                ->count();
            $coupleCount = Ghe::where('id_phong', $phongChieu->id)
                ->where('id_loai', $coupleSeatType->id)
                ->count();
            
            DB::commit();
            
            $this->info("✓ Đã tạo {$totalSeats} ghế:");
            $this->info("  - Ghế thường: {$normalCount}");
            $this->info("  - Ghế VIP: {$vipCount}");
            $this->info("  - Ghế đôi: {$coupleCount}");
            $this->info("\n✓ Hoàn thành! Phòng chiếu ID: {$phongChieu->id}");
            
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Lỗi: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}

