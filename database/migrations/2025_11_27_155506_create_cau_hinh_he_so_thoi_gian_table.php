<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cau_hinh_he_so_thoi_gian', function (Blueprint $table) {
            $table->id();
            $table->string('ten_quy_tac')->unique();
            $table->enum('loai', ['ngay_tuan', 'gio_chieu']);
            $table->string('gia_tri')->nullable(); // ví dụ: 1-5 hoặc 22:00-06:00
            $table->decimal('he_so', 5, 2)->default(1.00);
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cau_hinh_he_so_thoi_gian');
    }
};