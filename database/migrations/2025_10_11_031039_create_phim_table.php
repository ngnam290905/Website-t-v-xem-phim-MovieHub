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
        if (!Schema::hasTable('phim')) {
            Schema::create('phim', function (Blueprint $table) {
                $table->id();
                $table->string('ten_phim')->nullable();
                $table->integer('do_dai')->nullable();
                $table->string('poster')->nullable();
                $table->text('mo_ta')->nullable();
                $table->string('dao_dien', 100)->nullable();
                $table->text('dien_vien')->nullable();
                $table->string('trailer')->nullable();
                $table->boolean('trang_thai')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phim');
    }
};
