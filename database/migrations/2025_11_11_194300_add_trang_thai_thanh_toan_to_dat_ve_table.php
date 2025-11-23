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
            $table->tinyInteger('trang_thai_thanh_toan')->default(0)->after('trang_thai')->comment('0: Chưa thanh toán, 1: Đã thanh toán, 2: Đã hoàn tiền');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_ve', function (Blueprint $table) {
            $table->dropColumn('trang_thai_thanh_toan');
        });
    }
};
