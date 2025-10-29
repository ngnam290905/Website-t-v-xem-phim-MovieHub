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
            $table->dropForeign(['id_loai']);
            $table->dropColumn('id_loai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ghe', function (Blueprint $table) {
            $table->foreignId('id_loai')->constrained('loai_ghe');
        });
    }
};
