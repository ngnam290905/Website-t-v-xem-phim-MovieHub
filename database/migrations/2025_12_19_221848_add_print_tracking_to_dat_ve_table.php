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
        Schema::table('dat_ve', function (Blueprint $table) {
            $table->boolean('da_in')->default(false)->after('checked_in');
            $table->timestamp('thoi_gian_in')->nullable()->after('da_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_ve', function (Blueprint $table) {
            $table->dropColumn(['da_in', 'thoi_gian_in']);
        });
    }
};
