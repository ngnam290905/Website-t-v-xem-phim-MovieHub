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
        Schema::table('phim', function (Blueprint $table) {
            $table->decimal('doanh_thu', 15, 2)->nullable()->after('trang_thai')->comment('Tổng doanh thu từ phim (VNĐ)');
            $table->decimal('loi_nhuan', 15, 2)->nullable()->after('doanh_thu')->comment('Lợi nhuận ròng (VNĐ)');
            $table->text('mo_ta_ngan')->nullable()->after('mo_ta')->comment('Mô tả ngắn gọn về phim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phim', function (Blueprint $table) {
            $table->dropColumn(['doanh_thu', 'loi_nhuan', 'mo_ta_ngan']);
        });
    }
};
