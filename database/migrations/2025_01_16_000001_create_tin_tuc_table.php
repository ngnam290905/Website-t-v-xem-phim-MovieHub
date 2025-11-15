<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tin_tuc', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de');
            $table->string('slug')->unique();
            $table->text('tom_tat')->nullable();
            $table->longText('noi_dung');
            $table->string('hinh_anh')->nullable();
            $table->string('tac_gia')->nullable();
            $table->string('the_loai')->nullable();
            $table->integer('luot_xem')->default(0);
            $table->boolean('noi_bat')->default(false);
            $table->boolean('trang_thai')->default(true);
            $table->timestamp('ngay_dang')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('trang_thai');
            $table->index('noi_bat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tin_tuc');
    }
};

