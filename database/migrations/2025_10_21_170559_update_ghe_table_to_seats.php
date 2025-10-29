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
        Schema::table('ghe', function (Blueprint $table) {
            // Rename columns to match new structure
            $table->renameColumn('id_phong', 'room_id');
            $table->renameColumn('so_ghe', 'seat_code');
            $table->renameColumn('so_hang', 'row_label');
            $table->renameColumn('so_cot', 'col_number');
            
            // Add new columns
            $table->enum('type', ['normal', 'vip', 'disabled'])->default('normal')->after('col_number');
            $table->decimal('price', 10, 2)->nullable()->after('type');
            
            // Update status column to new enum values
            $table->dropColumn('status');
            $table->enum('status', ['available', 'booked', 'locked'])->default('available')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ghe', function (Blueprint $table) {
            // Revert column names
            $table->renameColumn('room_id', 'id_phong');
            $table->renameColumn('seat_code', 'so_ghe');
            $table->renameColumn('row_label', 'so_hang');
            $table->renameColumn('col_number', 'so_cot');
            $table->renameColumn('status', 'trang_thai');
            
            // Remove new columns
            $table->dropColumn('type');
            $table->dropColumn('status');
            $table->dropColumn('price');
            
            // Revert status to boolean
            $table->boolean('trang_thai')->default(true);
        });
    }
};
