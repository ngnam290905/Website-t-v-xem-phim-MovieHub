<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dat_ve', function (Blueprint $table) {
            // 1 = online, 2 = tai quay
            if (!Schema::hasColumn('dat_ve', 'phuong_thuc_thanh_toan')) {
                $table->tinyInteger('phuong_thuc_thanh_toan')->nullable()->after('trang_thai')->comment('1=online,2=tai_quay');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dat_ve', function (Blueprint $table) {
            if (Schema::hasColumn('dat_ve', 'phuong_thuc_thanh_toan')) {
                $table->dropColumn('phuong_thuc_thanh_toan');
            }
        });
    }
};
