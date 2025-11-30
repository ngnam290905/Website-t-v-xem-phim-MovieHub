<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->decimal('base_price', 10, 2);
            $table->timestamps();

            $table->index(['room_id', 'start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shows');
    }
};

