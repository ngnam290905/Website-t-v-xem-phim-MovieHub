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
            if (!Schema::hasColumn('dat_ve', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('created_at')->comment('Thời gian hết hạn cho vé offline (5 phút)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_ve', function (Blueprint $table) {
            if (Schema::hasColumn('dat_ve', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};
