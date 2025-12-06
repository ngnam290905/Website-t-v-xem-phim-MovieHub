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
        Schema::table('phim', function (Blueprint $table) {
            if (!Schema::hasColumn('phim', 'the_loai')) {
                $table->string('the_loai')->nullable()->after('ten_phim');
            }
            $hasCreated = Schema::hasColumn('phim', 'created_at');
            $hasUpdated = Schema::hasColumn('phim', 'updated_at');
            if (!$hasCreated && !$hasUpdated) {
                $table->timestamps();
            } else {
                if (!$hasCreated) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!$hasUpdated) {
                    $table->timestamp('updated_at')->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phim', function (Blueprint $table) {
            if (Schema::hasColumn('phim', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('phim', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('phim', 'the_loai')) {
                $table->dropColumn('the_loai');
            }
        });
    }
};
