<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('nguoi_dung', function (Blueprint $table) {
        if (!Schema::hasColumn('nguoi_dung', 'tong_chi_tieu')) {
            // Thêm cột tổng chi tiêu, mặc định là 0
            $table->decimal('tong_chi_tieu', 15, 0)->default(0)->after('password');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            if (Schema::hasColumn('nguoi_dung', 'tong_chi_tieu')) {
            $table->dropColumn('tong_chi_tieu');
        }
        });
    }
};
