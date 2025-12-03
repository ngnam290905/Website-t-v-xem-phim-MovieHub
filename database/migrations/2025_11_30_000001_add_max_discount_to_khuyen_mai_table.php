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
        Schema::table('khuyen_mai', function (Blueprint $table) {
            $table->decimal('gia_tri_giam_toi_da', 15, 2)->nullable()->after('gia_tri_giam')
                ->comment('Giá trị giảm tối đa (dùng cho loại giảm %)', );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khuyen_mai', function (Blueprint $table) {
            $table->dropColumn('gia_tri_giam_toi_da');
        });
    }
};
