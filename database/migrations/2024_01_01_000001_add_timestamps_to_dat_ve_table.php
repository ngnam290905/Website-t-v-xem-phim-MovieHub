<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dat_ve', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('dat_ve', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('phuong_thuc_thanh_toan');
            }
            if (!Schema::hasColumn('dat_ve', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });

        // Update existing records to have created_at set to current timestamp if null
        DB::statement('UPDATE dat_ve SET created_at = NOW() WHERE created_at IS NULL');
        DB::statement('UPDATE dat_ve SET updated_at = NOW() WHERE updated_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_ve', function (Blueprint $table) {
            if (Schema::hasColumn('dat_ve', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('dat_ve', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};
