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
        Schema::table('dat_ve', function (Blueprint $table) {
            $table->string('ticket_code', 50)->unique()->nullable()->after('id')->comment('Mã vé duy nhất');
            $table->text('qr_code')->nullable()->after('ticket_code')->comment('Mã QR code dạng base64 hoặc path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dat_ve', function (Blueprint $table) {
            $table->dropColumn(['ticket_code', 'qr_code']);
        });
    }
};
