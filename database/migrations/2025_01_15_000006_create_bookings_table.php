<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('show_id')->constrained('shows')->onDelete('cascade');
            $table->enum('status', ['PENDING', 'LOCKED', 'PAID', 'CANCELLED', 'EXPIRED'])->default('PENDING');
            $table->dateTime('lock_expires_at')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('payment_provider')->nullable();
            $table->string('payment_ref')->nullable();
            $table->timestamps();

            $table->index(['show_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

