<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bảng vai trò
        Schema::create('vai_tro', function (Blueprint $table) {
            $table->id();
            $table->string('ten', 100);
            $table->text('mo_ta')->nullable();
            $table->timestamps();
        });

        // Bảng người dùng
        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten', 100);
            $table->string('email', 100)->unique();
            $table->string('mat_khau', 255);
            $table->date('ngay_sinh')->nullable();
            $table->tinyInteger('gioi_tinh')->nullable();
            $table->string('sdt', 20)->nullable();
            $table->text('dia_chi')->nullable();
            $table->string('hinh_anh', 255)->nullable();
            $table->foreignId('id_vai_tro')->constrained('vai_tro');
            $table->tinyInteger('trang_thai')->default(1);
            $table->timestamps();
        });

        // Bảng hạng thành viên
        Schema::create('hang_thanh_vien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung');
            $table->string('ten_hang', 50);
            $table->text('uu_dai')->nullable();
            $table->integer('diem_toi_thieu');
            $table->timestamps();
        });

        // Bảng điểm thành viên
        Schema::create('diem_thanh_vien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung');
            $table->integer('tong_diem')->default(0);
            $table->date('ngay_het_han')->nullable();
            $table->timestamps();
        });

        // Bảng lịch sử điểm
        Schema::create('lich_su_diem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung');
            $table->string('ly_do', 255);
            $table->integer('diem_thay_doi');
            $table->date('ngay');
            $table->timestamps();
        });

        // Bảng khuyến mãi
        Schema::create('khuyen_mai', function (Blueprint $table) {
            $table->id();
            $table->string('ma_km', 50)->unique();
            $table->text('mo_ta')->nullable();
            $table->date('ngay_bat_dau');
            $table->date('ngay_ket_thuc');
            $table->decimal('gia_tri_giam', 10, 2);
            $table->text('dieu_kien')->nullable();
            $table->tinyInteger('trang_thai')->default(1);
            $table->timestamps();
        });

        // Bảng phim
        Schema::create('phim', function (Blueprint $table) {
            $table->id();
            $table->string('ten_phim', 255);
            $table->integer('do_dai');
            $table->string('poster', 255)->nullable();
            $table->text('mo_ta')->nullable();
            $table->string('dao_dien', 100)->nullable();
            $table->text('dien_vien')->nullable();
            $table->string('trailer', 255)->nullable();
            $table->tinyInteger('trang_thai')->default(1);
            $table->timestamps();
        });

        // Bảng phòng chiếu
        Schema::create('phong_chieu', function (Blueprint $table) {
            $table->id();
            $table->string('ten_phong', 100);
            $table->integer('so_hang');
            $table->integer('so_cot');
            $table->integer('suc_chua');
            $table->text('mo_ta')->nullable();
            $table->tinyInteger('trang_thai')->default(1);
            $table->timestamps();
        });

        // Bảng loại ghế
        Schema::create('loai_ghe', function (Blueprint $table) {
            $table->id();
            $table->string('ten_loai', 50);
            $table->decimal('he_so_gia', 5, 2);
            $table->timestamps();
        });

        // Bảng ghế
        Schema::create('ghe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_phong')->constrained('phong_chieu');
            $table->string('so_ghe', 10);
            $table->integer('so_hang');
            $table->foreignId('id_loai')->constrained('loai_ghe');
            $table->tinyInteger('trang_thai')->default(1);
            $table->timestamps();
        });

        // Bảng suất chiếu
        Schema::create('suat_chieu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_phim')->constrained('phim');
            $table->foreignId('id_phong')->constrained('phong_chieu');
            $table->datetime('thoi_gian_bat_dau');
            $table->datetime('thoi_gian_ket_thuc');
            $table->tinyInteger('trang_thai')->default(1);
            $table->timestamps();
        });

        // Bảng đặt vé
        Schema::create('dat_ve', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung');
            $table->foreignId('id_suat_chieu')->constrained('suat_chieu');
            $table->foreignId('id_khuyen_mai')->nullable()->constrained('khuyen_mai');
            $table->tinyInteger('trang_thai')->default(0);
            $table->timestamps();
        });

        // Bảng chi tiết đặt vé (ghế)
        Schema::create('chi_tiet_dat_ve', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dat_ve')->constrained('dat_ve');
            $table->foreignId('id_ghe')->constrained('ghe');
            $table->decimal('gia', 10, 2);
            $table->timestamps();
        });

        // Bảng combo
        Schema::create('combo', function (Blueprint $table) {
            $table->id();
            $table->string('ten_combo', 100);
            $table->text('mo_ta')->nullable();
            $table->decimal('gia', 10, 2);
            $table->string('hinh_anh', 255)->nullable();
            $table->tinyInteger('trang_thai')->default(1);
            $table->timestamps();
        });

        // Bảng chi tiết combo
        Schema::create('chi_tiet_combo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_combo')->constrained('combo');
            $table->string('ten_mon', 100);
            $table->integer('so_luong');
            $table->timestamps();
        });

        // Bảng đặt combo
        Schema::create('dat_combo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dat_ve')->constrained('dat_ve');
            $table->foreignId('id_combo')->constrained('combo');
            $table->integer('so_luong');
            $table->decimal('gia', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dat_combo');
        Schema::dropIfExists('chi_tiet_combo');
        Schema::dropIfExists('combo');
        Schema::dropIfExists('chi_tiet_dat_ve');
        Schema::dropIfExists('dat_ve');
        Schema::dropIfExists('suat_chieu');
        Schema::dropIfExists('ghe');
        Schema::dropIfExists('loai_ghe');
        Schema::dropIfExists('phong_chieu');
        Schema::dropIfExists('phim');
        Schema::dropIfExists('khuyen_mai');
        Schema::dropIfExists('lich_su_diem');
        Schema::dropIfExists('diem_thanh_vien');
        Schema::dropIfExists('hang_thanh_vien');
        Schema::dropIfExists('nguoi_dung');
        Schema::dropIfExists('vai_tro');
    }
};