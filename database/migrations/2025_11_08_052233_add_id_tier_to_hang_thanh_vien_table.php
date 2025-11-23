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
        Schema::table('hang_thanh_vien', function (Blueprint $table) {
            // Thêm cột id_tier để liên kết với bảng tier
            $table->unsignedBigInteger('id_tier')->nullable()->after('id_nguoi_dung');
            
            // Thêm foreign key
            $table->foreign('id_tier')->references('id')->on('tier')->onDelete('set null');
            
            // Thêm timestamps để theo dõi
            $table->timestamp('ngay_cap_nhat_hang')->nullable()->comment('Ngày cập nhật hạng gần nhất');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hang_thanh_vien', function (Blueprint $table) {
            $table->dropForeign(['id_tier']);
            $table->dropColumn(['id_tier', 'ngay_cap_nhat_hang']);
        });
    }
};
