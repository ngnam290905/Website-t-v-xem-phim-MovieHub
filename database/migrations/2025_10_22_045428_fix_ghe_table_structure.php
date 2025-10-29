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
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('ghe', 'id_loai')) {
                $table->unsignedBigInteger('id_loai')->nullable()->after('room_id');
            }
            if (!Schema::hasColumn('ghe', 'so_ghe')) {
                $table->integer('so_ghe')->nullable()->after('row_label');
            }
            if (!Schema::hasColumn('ghe', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ghe', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn(['id_loai', 'so_ghe', 'price']);
        });
    }
};
