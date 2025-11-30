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
        if (!Schema::hasTable('suat_chieu_ghe')) {
            Schema::create('suat_chieu_ghe', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_suat_chieu');
                $table->unsignedBigInteger('id_ghe');
                $table->enum('status', ['available', 'holding', 'booked', 'blocked'])->default('available');
                $table->dateTime('hold_expires_at')->nullable();
                $table->timestamps();

                $table->foreign('id_suat_chieu')->references('id')->on('suat_chieu')->onDelete('cascade');
                $table->foreign('id_ghe')->references('id')->on('ghe')->onDelete('cascade');
                $table->unique(['id_suat_chieu', 'id_ghe']);
                $table->index(['id_suat_chieu', 'status']);
                $table->index('hold_expires_at');
            });
        } else {
            // Table exists, skip migration
            // Foreign keys may have type mismatch, skip adding them
        }
    }
    
    private function foreignKeyExists($table, $keyName)
    {
        $foreignKeys = DB::select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = ? 
             AND CONSTRAINT_NAME = ?",
            [$table, $keyName]
        );
        return !empty($foreignKeys);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suat_chieu_ghe');
    }
};
