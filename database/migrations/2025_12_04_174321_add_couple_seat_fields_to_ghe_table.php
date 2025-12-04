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
        if (Schema::hasTable('ghe')) {
            Schema::table('ghe', function (Blueprint $table) {
                if (!Schema::hasColumn('ghe', 'is_double')) {
                    $table->boolean('is_double')->default(0)->after('id_loai');
                }
                if (!Schema::hasColumn('ghe', 'pair_id')) {
                    $table->unsignedInteger('pair_id')->nullable()->after('is_double');
                    $table->foreign('pair_id')->references('id')->on('ghe')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ghe')) {
            Schema::table('ghe', function (Blueprint $table) {
                if (Schema::hasColumn('ghe', 'pair_id')) {
                    $table->dropForeign(['pair_id']);
                    $table->dropColumn('pair_id');
                }
                if (Schema::hasColumn('ghe', 'is_double')) {
                    $table->dropColumn('is_double');
                }
            });
        }
    }
};
