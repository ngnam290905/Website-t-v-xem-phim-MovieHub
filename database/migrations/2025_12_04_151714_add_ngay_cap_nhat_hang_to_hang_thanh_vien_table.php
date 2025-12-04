<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hang_thanh_vien', function (Blueprint $table) {
            // Nếu bảng chưa có cột thì mới thêm
            if (!Schema::hasColumn('hang_thanh_vien', 'ngay_cap_nhat_hang')) {
                $table->date('ngay_cap_nhat_hang')->nullable()->after('diem_toi_thieu');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hang_thanh_vien', function (Blueprint $table) {
            if (Schema::hasColumn('hang_thanh_vien', 'ngay_cap_nhat_hang')) {
                $table->dropColumn('ngay_cap_nhat_hang');
            }
        });
    }
};
