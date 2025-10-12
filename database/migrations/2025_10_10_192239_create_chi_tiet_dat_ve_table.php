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
        Schema::create('chi_tiet_dat_ve', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dat_ve')->constrained('dat_ve');
            $table->foreignId('id_ghe')->constrained('ghe');
            $table->decimal('gia', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_dat_ve');
    }
};
