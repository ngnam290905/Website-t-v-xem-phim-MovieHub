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
        // Check if unique index already exists
        $indexExists = false;
        try {
            $indexes = \DB::select("SHOW INDEX FROM suat_chieu WHERE Key_name = 'suat_chieu_id_phong_id_phim_thoi_gian_bat_dau_unique'");
            $indexExists = !empty($indexes);
        } catch (\Exception $e) {
            // Table might not exist or error checking
        }
        
        if (!$indexExists) {
            Schema::table('suat_chieu', function (Blueprint $table) {
                // Add unique index to prevent exact duplicate showtimes
                // Same room + same movie + same start time = unique
                $table->unique(['id_phong', 'id_phim', 'thoi_gian_bat_dau'], 'suat_chieu_id_phong_id_phim_thoi_gian_bat_dau_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suat_chieu', function (Blueprint $table) {
            $table->dropUnique('suat_chieu_id_phong_id_phim_thoi_gian_bat_dau_unique');
        });
    }
};
