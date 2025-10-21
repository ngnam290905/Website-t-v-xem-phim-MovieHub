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
        Schema::create('dat_ve', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('users');
            $table->foreignId('id_suat_chieu')->constrained('suat_chieu');
            $table->unsignedBigInteger('id_khuyen_mai')->nullable();
            $table->boolean('trang_thai')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dat_ve');
    }
};
