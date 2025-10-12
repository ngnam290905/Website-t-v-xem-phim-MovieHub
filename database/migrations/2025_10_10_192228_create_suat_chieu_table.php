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
        Schema::create('suat_chieu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_phim')->constrained('phim');
            $table->foreignId('id_phong')->constrained('phong_chieu');
            $table->datetime('thoi_gian_bat_dau');
            $table->datetime('thoi_gian_ket_thuc');
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suat_chieu');
    }
};
