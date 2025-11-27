<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Phim;

class RecalculatePhimRevenue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'phim:recalculate-revenue {--id= : ID của phim cụ thể}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tính lại doanh thu và lợi nhuận cho phim từ dữ liệu thanh toán';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phimId = $this->option('id');

        if ($phimId) {
            // Tính lại cho 1 phim cụ thể
            $phim = Phim::find($phimId);
            if (!$phim) {
                $this->error("Không tìm thấy phim với ID: {$phimId}");
                return 1;
            }

            $this->info("Đang tính lại doanh thu cho phim: {$phim->ten_phim}");
            $phim->updateDoanhThuLoiNhuan();
            $this->info("✓ Doanh thu: " . number_format((float)$phim->doanh_thu, 0, ',', '.') . " VNĐ");
            $this->info("✓ Lợi nhuận: " . number_format((float)$phim->loi_nhuan, 0, ',', '.') . " VNĐ");
            $this->info("✓ Hoàn tất!");
        } else {
            // Tính lại cho tất cả phim
            $this->info("Đang tính lại doanh thu cho tất cả phim...");
            
            $phims = Phim::all();
            $bar = $this->output->createProgressBar($phims->count());
            $bar->start();

            $totalRevenue = 0;
            $totalProfit = 0;

            foreach ($phims as $phim) {
                $phim->updateDoanhThuLoiNhuan();
                $totalRevenue += $phim->doanh_thu ?? 0;
                $totalProfit += $phim->loi_nhuan ?? 0;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            
            $this->info("✓ Đã cập nhật doanh thu cho {$phims->count()} phim");
            $this->info("📊 Tổng doanh thu: " . number_format($totalRevenue, 0, ',', '.') . " VNĐ");
            $this->info("💰 Tổng lợi nhuận: " . number_format($totalProfit, 0, ',', '.') . " VNĐ");
        }

        return 0;
    }
}
