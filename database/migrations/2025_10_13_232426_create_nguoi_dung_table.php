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
        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten', 100);
            $table->string('email', 100)->unique();
            $table->string('mat_khau', 255);
            $table->date('ngay_sinh')->nullable();
            $table->boolean('gioi_tinh')->nullable();
            $table->string('sdt', 20)->nullable();
            $table->text('dia_chi')->nullable();
            $table->string('hinh_anh', 255)->nullable();
            $table->unsignedBigInteger('id_vai_tro')->nullable();
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
            
            $table->foreign('id_vai_tro')->references('id')->on('vai_tro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nguoi_dung');
    }
};
