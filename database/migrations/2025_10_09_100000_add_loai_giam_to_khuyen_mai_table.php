<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('khuyen_mai', function (Blueprint $table) {
            $table->enum('loai_giam', ['phantram', 'codinh'])->default('phantram')->after('gia_tri_giam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khuyen_mai', function (Blueprint $table) {
            $table->dropColumn('loai_giam');
        });
    }
};
