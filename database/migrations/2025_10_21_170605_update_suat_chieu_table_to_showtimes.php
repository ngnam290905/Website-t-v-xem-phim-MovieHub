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
        Schema::table('suat_chieu', function (Blueprint $table) {
            // Rename columns to match new structure
            $table->renameColumn('id_phim', 'movie_id');
            $table->renameColumn('id_phong', 'room_id');
            $table->renameColumn('thoi_gian_bat_dau', 'start_time');
            $table->renameColumn('thoi_gian_ket_thuc', 'end_time');
            $table->renameColumn('trang_thai', 'status');
            
            // Update status to enum
            $table->dropColumn('status');
            $table->enum('status', ['coming', 'ongoing', 'finished'])->default('coming')->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suat_chieu', function (Blueprint $table) {
            // Revert column names
            $table->renameColumn('movie_id', 'id_phim');
            $table->renameColumn('room_id', 'id_phong');
            $table->renameColumn('start_time', 'thoi_gian_bat_dau');
            $table->renameColumn('end_time', 'thoi_gian_ket_thuc');
            $table->renameColumn('status', 'trang_thai');
            
            // Revert status to boolean
            $table->dropColumn('status');
            $table->boolean('trang_thai')->default(true);
        });
    }
};
