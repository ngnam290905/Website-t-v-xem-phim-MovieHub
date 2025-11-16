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
        if (!Schema::hasTable('khuyen_mai') || Schema::hasColumn('khuyen_mai', 'loai_giam')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        Schema::table('khuyen_mai', function (Blueprint $table) use ($driver) {
            if ($driver === 'sqlite') {
                $table->string('loai_giam')->default('phantram');
                return;
            }

            $table->enum('loai_giam', ['phantram', 'codinh'])->default('phantram')->after('gia_tri_giam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('khuyen_mai') || !Schema::hasColumn('khuyen_mai', 'loai_giam')) {
            return;
        }

        Schema::table('khuyen_mai', function (Blueprint $table) {
            $table->dropColumn('loai_giam');
        });
    }
};
