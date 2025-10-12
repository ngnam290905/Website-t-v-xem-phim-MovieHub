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
        Schema::create('combo', function (Blueprint $table) {
            $table->id();
            $table->string('ten', 100);
            $table->text('mo_ta')->nullable();
            $table->decimal('gia', 10, 2);
            $table->decimal('gia_khuyen_mai', 10, 2)->nullable();
            $table->boolean('trang_thai')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo');
    }
};
