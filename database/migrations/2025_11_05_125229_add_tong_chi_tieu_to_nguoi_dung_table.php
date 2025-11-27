<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->decimal('tong_chi_tieu', 15, 2)->default(0)->after('hinh_anh');
        });
    }

    public function down()
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->dropColumn('tong_chi_tieu');
        });
    }
};