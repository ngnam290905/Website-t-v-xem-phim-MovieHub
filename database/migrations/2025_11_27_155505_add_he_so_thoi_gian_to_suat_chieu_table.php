<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suat_chieu', function (Blueprint $table) {
            $table->decimal('he_so_thoi_gian', 5, 2)->default(1.00)->after('thoi_gian_ket_thuc');
        });
    }

    public function down(): void
    {
        Schema::table('suat_chieu', function (Blueprint $table) {
            $table->dropColumn('he_so_thoi_gian');
        });
    }
};