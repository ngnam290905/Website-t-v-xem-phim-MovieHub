<?php
// app/Helpers/PriceCalculator.php

use App\Models\SuatChieu;
use App\Models\Phim;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

function tinhGiaVe($id_suat_chieu, $id_loai_ghe)
{
    $suat = SuatChieu::findOrFail($id_suat_chieu);
    $phim = Phim::findOrFail($suat->id_phim);
    $loaiGhe = DB::table('loai_ghe')->where('id', $id_loai_ghe)->first();

    if (!$loaiGhe) $loaiGhe = (object)['he_so' => 1.0];

    $giaCoBan = $phim->gia_co_ban ?? 100000;
    $ngayChieu = Carbon::parse($suat->ngay_chieu);
    $gioBatDau = Carbon::parse($suat->gio_bat_dau)->format('H:i');

    // 1. Tính hệ số thời gian
    $heSoThoiGian = 1.0;
    $thu = $ngayChieu->dayOfWeekIso; // 1 = Thứ 2, 7 = Chủ nhật

    $rules = DB::table('cau_hinh_he_so_thoi_gian')
        ->where('trang_thai', true)
        ->get();

    foreach ($rules as $rule) {
        if ($rule->loai == 'ngay_tuan') {
            [$batDau, $ketThuc] = explode('-', $rule->gia_tri);
            $days = range($batDau, $ketThuc);
            if (in_array($thu, $days) || ($batDau > $ketThuc && ($thu >= $batDau || $thu <= $ketThuc))) {
                $heSoThoiGian = $rule->he_so;
            }
        }

        if ($rule->loai == 'gio_chieu') {
            [$gioBD, $gioKT] = explode('-', $rule->gia_tri);
            if ($gioBD <= $gioKT) {
                if ($gioBatDau >= $gioBD && $gioBatDau < $gioKT) {
                    $heSoThoiGian = $rule->he_so;
                }
            } else {
                if ($gioBatDau >= $gioBD || $gioBatDau < $gioKT) {
                    $heSoThoiGian = $rule->he_so;
                }
            }
        }
    }

    // 2. Tính giá sau hệ số ghế + thời gian
    $giaSauHeSo = $giaCoBan * $heSoThoiGian * $loaiGhe->he_so;

    // 3. Áp dụng khuyến mãi (nếu có và còn hiệu lực)
    $khuyenMai = DB::table('khuyen_mai')
        ->where('id_phim', $suat->id_phim)
        ->where('ngay_bat_dau', '<=', $ngayChieu)
        ->where('ngay_ket_thuc', '>=', $ngayChieu)
        ->where('trang_thai', 1)
        ->first();

    $giamPhanTram = 0;
    if ($khuyenMai) {
        if ($khuyenMai->loai_giam == 'phantram') {
            $giamPhanTram = $khuyenMai->gia_tri_giam / 100;
        } else {
            // cố định → tính % giảm so với giá hiện tại
            $giamPhanTram = $khuyenMai->gia_tri_giam / $giaSauHeSo;
        }
    }

    $giaCuoiCung = $giaSauHeSo * (1 - $giamPhanTram);

    return [
        'gia_co_ban' => $giaCoBan,
        'he_so_thoi_gian' => $heSoThoiGian,
        'he_so_ghe' => $loaiGhe->he_so,
        'giam_phan_tram' => $giamPhanTram * 100,
        'gia_cuoi_cung' => round($giaCuoiCung / 1000) * 1000, // làm tròn về 1.000
    ];
}