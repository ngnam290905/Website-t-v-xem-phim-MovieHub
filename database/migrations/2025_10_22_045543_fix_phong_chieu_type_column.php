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
        Schema::table('phong_chieu', function (Blueprint $table) {
            // Change type column to varchar(50) to accommodate longer values
            $table->string('type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phong_chieu', function (Blueprint $table) {
            // Revert type column to original size
            $table->string('type', 10)->change();
        });
    }
};
