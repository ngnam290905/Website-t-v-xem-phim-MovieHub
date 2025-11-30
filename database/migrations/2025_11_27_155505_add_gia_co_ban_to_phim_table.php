<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phim', function (Blueprint $table) {
            $table->decimal('gia_co_ban', 15, 2)->default(100000)->after('doanh_thu');
        });
    }

    public function down(): void
    {
        Schema::table('phim', function (Blueprint $table) {
            $table->dropColumn('gia_co_ban');
        });
    }
};