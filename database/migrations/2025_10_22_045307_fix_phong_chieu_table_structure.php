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
            // Drop old columns that are not needed
            $table->dropColumn(['suc_chua']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phong_chieu', function (Blueprint $table) {
            // Add back the column if needed
            $table->integer('suc_chua')->nullable();
        });
    }
};
