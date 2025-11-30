<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ghe')) {
            Schema::table('ghe', function (Blueprint $table) {
                if (!Schema::hasColumn('ghe', 'pos_x')) {
                    $table->integer('pos_x')->nullable()->after('trang_thai');
                }
                if (!Schema::hasColumn('ghe', 'pos_y')) {
                    $table->integer('pos_y')->nullable()->after('pos_x');
                }
                if (!Schema::hasColumn('ghe', 'zone')) {
                    $table->string('zone', 50)->nullable()->after('pos_y');
                }
                if (!Schema::hasColumn('ghe', 'meta')) {
                    $table->json('meta')->nullable()->after('zone');
                }
            });
        }

        if (Schema::hasTable('phong_chieu')) {
            Schema::table('phong_chieu', function (Blueprint $table) {
                if (!Schema::hasColumn('phong_chieu', 'layout_json')) {
                    $table->json('layout_json')->nullable()->after('mo_ta');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ghe')) {
            Schema::table('ghe', function (Blueprint $table) {
                if (Schema::hasColumn('ghe', 'meta')) {
                    $table->dropColumn('meta');
                }
                if (Schema::hasColumn('ghe', 'zone')) {
                    $table->dropColumn('zone');
                }
                if (Schema::hasColumn('ghe', 'pos_y')) {
                    $table->dropColumn('pos_y');
                }
                if (Schema::hasColumn('ghe', 'pos_x')) {
                    $table->dropColumn('pos_x');
                }
            });
        }

        if (Schema::hasTable('phong_chieu')) {
            Schema::table('phong_chieu', function (Blueprint $table) {
                if (Schema::hasColumn('phong_chieu', 'layout_json')) {
                    $table->dropColumn('layout_json');
                }
            });
        }
    }
};


