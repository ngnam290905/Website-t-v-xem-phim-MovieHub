<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['INIT', 'SUCCESS', 'FAIL'])->default('INIT');
            $table->string('provider');
            $table->string('transaction_id')->nullable()->unique();
            $table->dateTime('paid_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

