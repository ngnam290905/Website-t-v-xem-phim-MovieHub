<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\NguoiDung;
use App\Models\Tier;

class ThanhVienController extends Controller
{
    /**
     * Hiển thị form đăng ký thành viên
     */
    public function showRegistrationForm()
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        
        // Kiểm tra đã là thành viên chưa
        if ($user && $user->la_thanh_vien) {
            return redirect()->route('thanh-vien.profile')->with('info', 'Bạn đã là thành viên rồi!');
        }
        
        return view('thanh-vien.register');
    }

    /**
     * Xử lý đăng ký thành viên
     */
    public function register(Request $request)
    {
        $request->validate([
            'dong_y_dieu_khoan' => 'required|accepted',
        ], [
            'dong_y_dieu_khoan.required' => 'Bạn phải đồng ý với điều khoản và điều kiện',
            'dong_y_dieu_khoan.accepted' => 'Bạn phải đồng ý với điều khoản và điều kiện',
        ]);

        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();

        // Kiểm tra đã là thành viên
        if ($user->la_thanh_vien) {
            return redirect()->route('thanh-vien.profile')->with('info', 'Bạn đã là thành viên rồi!');
        }

        DB::beginTransaction();
        try {
            // Cập nhật trạng thái thành viên
            $user->update([
                'la_thanh_vien' => 1,
                'ngay_dang_ky_thanh_vien' => now(),
            ]);

            // Lấy tier Đồng (tier đầu tiên)
            $tierDong = Tier::where('ten_hang', 'Đồng')->first();

            // Tạo bản ghi hạng thành viên với id_tier
            DB::table('hang_thanh_vien')->insert([
                'id_nguoi_dung' => $user->id,
                'id_tier' => $tierDong->id,
                'ten_hang' => $tierDong->ten_hang,
                'uu_dai' => $tierDong->uu_dai,
                'diem_toi_thieu' => $tierDong->diem_toi_thieu,
                'ngay_cap_nhat_hang' => now(),
            ]);

            // Tạo bản ghi điểm thành viên
            DB::table('diem_thanh_vien')->insert([
                'id_nguoi_dung' => $user->id,
                'tong_diem' => 0,
                'ngay_het_han' => now()->addYear(), // Điểm có hiệu lực 1 năm
            ]);

            DB::commit();

            return redirect()->route('thanh-vien.profile')->with('success', 'Đăng ký thành viên thành công! Bạn đã là thành viên hạng Đồng.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra. Vui lòng thử lại!');
        }
    }

    /**
     * Hiển thị thông tin thành viên
     */
    public function profile()
    {
        /** @var \App\Models\NguoiDung $user */
        $user = Auth::user();
        
        // Kiểm tra có phải thành viên không
        if (!$user->la_thanh_vien) {
            return redirect()->route('thanh-vien.register-form')->with('info', 'Bạn chưa đăng ký thành viên!');
        }

        // Lấy thông tin hạng thành viên
        $hangThanhVien = DB::table('hang_thanh_vien')
            ->where('id_nguoi_dung', $user->id)
            ->first();

        // Lấy thông tin điểm
        $diemThanhVien = DB::table('diem_thanh_vien')
            ->where('id_nguoi_dung', $user->id)
            ->first();

        // Lấy lịch sử đặt vé
        $lichSuDatVe = DB::table('dat_ve')
            ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
            ->join('phim', 'suat_chieu.id_phim', '=', 'phim.id')
            ->where('dat_ve.id_nguoi_dung', $user->id)
            ->select('phim.ten_phim', 'phim.poster', 'suat_chieu.ngay_chieu', 'suat_chieu.gio_chieu', 'dat_ve.trang_thai', 'dat_ve.created_at')
            ->orderBy('dat_ve.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('thanh-vien.profile', compact('user', 'hangThanhVien', 'diemThanhVien', 'lichSuDatVe'));
    }
}
