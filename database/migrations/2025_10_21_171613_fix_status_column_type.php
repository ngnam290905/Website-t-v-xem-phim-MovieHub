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
            $table->dropColumn('status');
            $table->enum('status', ['available', 'booked', 'locked'])->default('available')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ghe', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->boolean('status')->default(true);
        });
    }
};
