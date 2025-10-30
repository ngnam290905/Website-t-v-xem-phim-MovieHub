<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('chi_tiet_dat_ve', function (Blueprint $table) {
            $table->decimal('gia_ve', 10, 2)->nullable();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chi_tiet_dat_ve', function (Blueprint $table) {
            //
        });
    }
};
