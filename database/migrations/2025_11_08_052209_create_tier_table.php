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
        Schema::create('tier', function (Blueprint $table) {
            $table->id();
            $table->string('ten_hang', 50)->comment('Tên hạng: Bronze, Silver, Gold, Platinum');
            $table->text('mo_ta')->nullable()->comment('Mô tả chi tiết hạng');
            $table->text('uu_dai')->nullable()->comment('Các ưu đãi của hạng');
            $table->integer('diem_toi_thieu')->default(0)->comment('Điểm tối thiểu để đạt hạng');
            $table->integer('diem_toi_da')->nullable()->comment('Điểm tối đa của hạng (NULL = không giới hạn)');
            $table->decimal('giam_gia_ve', 5, 2)->default(0)->comment('% giảm giá vé phim');
            $table->decimal('giam_gia_combo', 5, 2)->default(0)->comment('% giảm giá combo');
            $table->decimal('ty_le_tich_diem', 5, 2)->default(1.0)->comment('Tỷ lệ tích điểm (1.0 = 100%)');
            $table->integer('so_thu_tu')->default(0)->comment('Thứ tự hiển thị');
            $table->string('mau_sac', 20)->nullable()->comment('Mã màu đại diện (#hex)');
            $table->string('icon', 100)->nullable()->comment('Icon/hình ảnh đại diện');
            $table->tinyInteger('trang_thai')->default(1)->comment('1: Hoạt động, 0: Không hoạt động');
            $table->timestamps();
            
            // Index
            $table->index('so_thu_tu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tier');
    }
};
