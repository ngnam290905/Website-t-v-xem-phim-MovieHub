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
        Schema::table('nguoi_dung', function (Blueprint $table) {
            // Thêm cột kiểm tra người dùng có phải là thành viên hay không
            $table->tinyInteger('la_thanh_vien')->default(0)->after('id_vai_tro')->comment('0: Không phải thành viên, 1: Là thành viên');
            
            // Thêm cột ngày đăng ký thành viên
            $table->date('ngay_dang_ky_thanh_vien')->nullable()->after('la_thanh_vien')->comment('Ngày đăng ký thành viên');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->dropColumn(['la_thanh_vien', 'ngay_dang_ky_thanh_vien']);
        });
    }
};
