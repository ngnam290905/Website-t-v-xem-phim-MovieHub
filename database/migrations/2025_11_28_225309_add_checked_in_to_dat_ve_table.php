<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dat_ve', function (Blueprint $table) {
            if (!Schema::hasColumn('dat_ve', 'checked_in')) {
                $table->boolean('checked_in')->default(false)->after('trang_thai')->comment('Đã quét vé chưa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dat_ve', function (Blueprint $table) {
            if (Schema::hasColumn('dat_ve', 'checked_in')) {
                $table->dropColumn('checked_in');
            }
        });
    }
};
