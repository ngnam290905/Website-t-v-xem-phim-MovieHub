<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHotColumnToPhimTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasColumn('phim', 'hot')) {
            Schema::table('phim', function (Blueprint $table) {
                $table->boolean('hot')->default(false)->after('trang_thai');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('phim', 'hot')) {
            Schema::table('phim', function (Blueprint $table) {
                $table->dropColumn('hot');
            });
        }
    }
}
