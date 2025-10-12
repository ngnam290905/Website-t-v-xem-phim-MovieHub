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
        Schema::create('ghe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_phong')->constrained('phong_chieu');
            $table->string('so_ghe', 10);
            $table->integer('so_hang');
            $table->foreignId('id_loai')->constrained('loai_ghe');
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ghe');
    }
};
