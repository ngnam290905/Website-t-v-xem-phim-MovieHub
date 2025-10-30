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
        if (!Schema::hasTable('loai_ghe')) {
            Schema::create('loai_ghe', function (Blueprint $table) {
                $table->id();
                $table->string('ten_loai');
                $table->decimal('he_so_gia', 4, 2)->default(1.00);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loai_ghe');
    }
};
