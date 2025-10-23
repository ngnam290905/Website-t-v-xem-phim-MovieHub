<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists and has data
        if (Schema::hasTable('phim')) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('phim', 'ten_goc')) {
                Schema::table('phim', function (Blueprint $table) {
                    $table->string('ten_goc')->nullable()->after('ten_phim');
                });
            }
            
            if (!Schema::hasColumn('phim', 'the_loai')) {
                Schema::table('phim', function (Blueprint $table) {
                    $table->string('the_loai')->nullable()->after('dien_vien');
                });
            }
            
            if (!Schema::hasColumn('phim', 'quoc_gia')) {
                Schema::table('phim', function (Blueprint $table) {
                    $table->string('quoc_gia')->nullable()->after('the_loai');
                });
            }
            
            if (!Schema::hasColumn('phim', 'ngon_ngu')) {
                Schema::table('phim', function (Blueprint $table) {
                    $table->string('ngon_ngu')->nullable()->after('quoc_gia');
                });
            }
            
            if (!Schema::hasColumn('phim', 'do_tuoi')) {
                Schema::table('phim', function (Blueprint $table) {
                    $table->string('do_tuoi', 10)->nullable()->after('ngon_ngu');
                });
            }
            
            if (!Schema::hasColumn('phim', 'ngay_khoi_chieu')) {
                Schema::table('phim', function (Blueprint $table) {
                    $table->date('ngay_khoi_chieu')->nullable()->after('do_dai');
                });
            }
            
            if (!Schema::hasColumn('phim', 'ngay_ket_thuc')) {
                Schema::table('phim', function (Blueprint $table) {
                    $table->date('ngay_ket_thuc')->nullable()->after('ngay_khoi_chieu');
                });
            }
            
            if (!Schema::hasColumn('phim', 'diem_danh_gia')) {
                Schema::table('phim', function (Blueprint $table) {
                    $table->decimal('diem_danh_gia', 3, 1)->nullable()->after('mo_ta');
                });
            }
            
            if (!Schema::hasColumn('phim', 'so_luot_danh_gia')) {
                Schema::table('phim', function (Blueprint $table) {
                    $table->integer('so_luot_danh_gia')->default(0)->after('diem_danh_gia');
                });
            }
            
            if (!Schema::hasColumn('phim', 'created_at')) {
                Schema::table('phim', function (Blueprint $table) {
                    $table->timestamps();
                });
            }

            if (!Schema::hasColumn('phim', 'deleted_at')) {
                Schema::table('phim', function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
            
            // Handle trang_thai conversion safely
            if (Schema::hasColumn('phim', 'trang_thai')) {
                // Check if it's already enum
                $columnType = DB::select("SHOW COLUMNS FROM phim LIKE 'trang_thai'")[0]->Type ?? '';
                
                if (strpos($columnType, 'enum') === false) {
                    // Convert boolean to enum
                    DB::statement("ALTER TABLE phim MODIFY COLUMN trang_thai ENUM('sap_chieu','dang_chieu','ngung_chieu') DEFAULT 'sap_chieu'");
                    
                    // Update existing data
                    DB::statement("UPDATE phim SET trang_thai = CASE WHEN trang_thai = 1 THEN 'dang_chieu' ELSE 'sap_chieu' END");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate boolean column and map enum back to boolean
        Schema::table('phim', function (Blueprint $table) {
            $table->boolean('trang_thai')->default(true)->after('trailer');
        });

        DB::statement("UPDATE `phim` SET trang_thai = CASE WHEN trang_thai = 'dang_chieu' THEN 1 ELSE 0 END");

        Schema::table('phim', function (Blueprint $table) {
            // Drop the enum status column
            $table->dropColumn('trang_thai');

            // Drop added columns
            $table->dropColumn([
                'ten_goc',
                'the_loai',
                'quoc_gia',
                'ngon_ngu',
                'do_tuoi',
                'ngay_khoi_chieu',
                'ngay_ket_thuc',
                'diem_danh_gia',
                'so_luot_danh_gia',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);
        });
    }
};


