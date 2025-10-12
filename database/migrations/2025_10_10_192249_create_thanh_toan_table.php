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
        Schema::create('thanh_toan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dat_ve')->constrained('dat_ve');
            $table->string('phuong_thuc', 50);
            $table->decimal('so_tien', 10, 2);
            $table->string('ma_giao_dich', 100)->nullable();
            $table->boolean('trang_thai')->default(false);
            $table->datetime('thoi_gian')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thanh_toan');
    }
};
