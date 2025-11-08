<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dat_ve', function (Blueprint $table) {
            // Thêm các cột tính toán chi tiết
            $table->decimal('tong_tien_goc', 15, 2)->default(0)->after('id_khuyen_mai')->comment('Tổng tiền trước giảm giá');
            $table->decimal('tien_giam_khuyen_mai', 15, 2)->default(0)->after('tong_tien_goc')->comment('Tiền giảm từ mã khuyến mãi');
            $table->decimal('tien_giam_thanh_vien', 15, 2)->default(0)->after('tien_giam_khuyen_mai')->comment('Tiền giảm từ hạng thành viên');
            $table->integer('diem_su_dung')->default(0)->after('tien_giam_thanh_vien')->comment('Điểm đã sử dụng');
            $table->decimal('tien_giam_diem', 15, 2)->default(0)->after('diem_su_dung')->comment('Tiền giảm từ điểm (100 điểm = 10.000đ)');
            $table->integer('diem_tich_luy')->default(0)->after('tien_giam_diem')->comment('Điểm tích lũy được từ đơn');
            $table->decimal('tong_tien', 15, 2)->default(0)->after('diem_tich_luy')->comment('Tổng tiền phải thanh toán');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_ve', function (Blueprint $table) {
            $table->dropColumn([
                'tong_tien_goc',
                'tien_giam_khuyen_mai',
                'tien_giam_thanh_vien',
                'diem_su_dung',
                'tien_giam_diem',
                'diem_tich_luy',
                'tong_tien'
            ]);
        });
    }
};
