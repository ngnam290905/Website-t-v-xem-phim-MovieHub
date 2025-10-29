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
        Schema::table('phong_chieu', function (Blueprint $table) {
            // Rename columns to match new structure
            $table->renameColumn('ten_phong', 'name');
            $table->renameColumn('so_hang', 'rows');
            $table->renameColumn('so_cot', 'cols');
            $table->renameColumn('mo_ta', 'description');
            $table->renameColumn('trang_thai', 'status');
            
            // Add new columns
            $table->enum('type', ['normal', 'vip', '3d', 'imax'])->default('normal')->after('cols');
            
            // Update status to enum
            $table->dropColumn('status');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phong_chieu', function (Blueprint $table) {
            // Revert column names
            $table->renameColumn('name', 'ten_phong');
            $table->renameColumn('rows', 'so_hang');
            $table->renameColumn('cols', 'so_cot');
            $table->renameColumn('description', 'mo_ta');
            $table->renameColumn('status', 'trang_thai');
            
            // Remove new columns
            $table->dropColumn('type');
            
            // Revert status to boolean
            $table->dropColumn('status');
            $table->boolean('trang_thai')->default(true);
        });
    }
};
