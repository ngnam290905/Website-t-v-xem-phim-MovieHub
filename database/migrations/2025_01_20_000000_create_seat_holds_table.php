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
        Schema::create('seat_holds', function (Blueprint $table) {
            $table->id();

            // Dùng unsignedInteger để tương thích với bảng hiện có (ghe/suat_chieu dạng INT)
            $table->unsignedInteger('showtime_id'); // id_suat_chieu
            $table->unsignedInteger('seat_id');     // id_ghe
            $table->unsignedInteger('user_id')->nullable(); // id_nguoi_dung (nullable)
            $table->string('session_id', 100)->nullable();  // Cho khách chưa đăng nhập

            // Thời điểm hết hạn giữ ghế (10 phút kể từ lúc tạo trong service)
            $table->dateTime('expires_at');

            $table->timestamps();

            // Chỉ mục phục vụ tra cứu nhanh
            $table->index(['showtime_id', 'seat_id']);
            $table->index('expires_at');
            $table->index('user_id');
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_holds');
    }
};

