<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\VaiTro;
use App\Models\HangThanhVien; // Đúng model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        // Subqueries tính tổng tiền ghế + combo
        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
            ->groupBy('id_dat_ve');

        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong,1)) as combo_total'))
            ->groupBy('id_dat_ve');

        $agg = DB::table('dat_ve as v')
            ->leftJoinSub($seatSub, 's', function($j){ 
                $j->on('s.id_dat_ve','=','v.id'); 
            })
            ->leftJoinSub($comboSub, 'c', function($j){ 
                $j->on('c.id_dat_ve','=','v.id'); 
            })
            ->select(
                'v.id_nguoi_dung',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('MAX(v.created_at) as last_active'),
                DB::raw('SUM(COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)) as total_spent')
            )
            ->where('v.trang_thai', 1)
            ->groupBy('v.id_nguoi_dung');

        $users = NguoiDung::query()
            ->with(['vaiTro', 'diemThanhVien', 'hangThanhVien'])
            ->leftJoinSub($agg, 'a', function($j){ 
                $j->on('a.id_nguoi_dung','=','nguoi_dung.id'); 
            })
            ->whereNull('nguoi_dung.deleted_at')
            ->select(
                'nguoi_dung.*',
                DB::raw('COALESCE(a.total_orders,0) as total_orders'),
                DB::raw('COALESCE(a.total_spent,0) as total_spent'),
                DB::raw('a.last_active')
            )
            ->orderByDesc('nguoi_dung.id')
            ->paginate(10);

        // Tổng người dùng
        $totalUsers = NguoiDung::whereNull('deleted_at')->count();

        // Người dùng hoạt động 30 ngày
        $active30Days = DB::table('dat_ve')
            ->where('trang_thai', 1)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->distinct('id_nguoi_dung')
            ->count('id_nguoi_dung');

        // THỐNG KÊ HẠNG THÀNH VIÊN - HOẠT ĐỘNG ĐÚNG 100% VỚI BẢNG CỦA BẠN
        $tierStats = DB::table('nguoi_dung as nd')
            ->leftJoin('hang_thanh_vien as h', 'h.id_nguoi_dung', '=', 'nd.id')
            ->leftJoin('tier as t', 'h.id_tier', '=', 't.id')
            ->whereNull('nd.deleted_at')
            ->selectRaw('COALESCE(t.ten_hang, "Chưa có hạng") as ten_hang, COUNT(*) as total')
            ->groupBy('h.id_tier', 't.ten_hang')
            ->pluck('total', 'ten_hang');

        $tierDong      = $tierStats['Đồng'] ?? 0;
        $tierBac       = $tierStats['Bạc'] ?? 0;
        $tierVang      = $tierStats['Vàng'] ?? 0;
        $tierKimCuong  = $tierStats['Kim cương'] ?? $tierStats['Kim Cương'] ?? 0;

        return view('admin.users.index', compact(
            'users',
            'totalUsers',
            'active30Days',
            'tierDong',
            'tierBac',
            'tierVang',
            'tierKimCuong'
        ));
    }

    public function create()
    {
        $roles = VaiTro::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ho_ten' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:nguoi_dung,email',
            'mat_khau' => 'required|string|min:6|confirmed',
            'sdt' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string|max:255',
            'id_vai_tro' => 'required|exists:vai_tro,id',
            'trang_thai' => 'boolean',
        ]);

        $validated['mat_khau'] = Hash::make($validated['mat_khau']);
        $validated['trang_thai'] = $request->has('trang_thai') ? 1 : 0;

        NguoiDung::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Tạo tài khoản thành công.');
    }

    public function edit($id)
    {
        $user = NguoiDung::findOrFail($id);
        $roles = VaiTro::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = NguoiDung::findOrFail($id);

        $validated = $request->validate([
            'ho_ten' => 'required|string|max:100',
            'email' => [
                'required', 'email', 'max:100',
                Rule::unique('nguoi_dung', 'email')->ignore($user->id)
            ],
            'mat_khau' => 'nullable|string|min:6|confirmed',
            'sdt' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string|max:255',
            'id_vai_tro' => 'required|exists:vai_tro,id',
            'trang_thai' => 'boolean',
        ]);

        $data = $validated;

        if ($request->filled('mat_khau')) {
            $data['mat_khau'] = Hash::make($validated['mat_khau']);
        } else {
            unset($data['mat_khau']);
        }

        $data['trang_thai'] = $request->has('trang_thai') ? 1 : 0;

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Cập nhật tài khoản thành công.');
    }

    public function show($id)
    {
        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
            ->groupBy('id_dat_ve');

        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong,1)) as combo_total'))
            ->groupBy('id_dat_ve');

        $agg = DB::table('dat_ve as v')
            ->leftJoinSub($seatSub, 's', function($j){ 
                $j->on('s.id_dat_ve','=','v.id'); 
            })
            ->leftJoinSub($comboSub, 'c', function($j){ 
                $j->on('c.id_dat_ve','=','v.id'); 
            })
            ->where('v.trang_thai', 1)
            ->where('v.id_nguoi_dung', $id)
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('MAX(v.created_at) as last_active'),
                DB::raw('SUM(COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)) as total_spent')
            )
            ->first();

        $user = NguoiDung::with(['vaiTro','diemThanhVien','hangThanhVien'])
            ->findOrFail($id);

        $user->total_orders = (int)($agg->total_orders ?? 0);
        $user->total_spent = (float)($agg->total_spent ?? 0);
        $user->last_active = $agg->last_active ?? null;

        return view('admin.users.show', compact('user'));
    }

    // Hàm cập nhật hạng thành viên - ĐÃ SỬA ĐÚNG MODEL
    public function capNhatHangThanhVien($userId, $tongDiem)
    {
        $dsHang = HangThanhVien::orderBy('diem_toi_thieu', 'asc')->get();

        $hangHienTai = null;
        foreach ($dsHang as $hang) {
            if ($tongDiem >= $hang->diem_toi_thieu) {
                $hangHienTai = $hang;
            }
        }

        if ($hangHienTai) {
            HangThanhVien::updateOrCreate(
                ['id_nguoi_dung' => $userId],
                [
                    'id_tier' => $hangHienTai->id_tier,
                    'uu_dai' => $hangHienTai->uu_dai,
                    'diem_toi_thieu' => $hangHienTai->diem_toi_thieu,
                    'ngay_cap_nhat_hang' => now(),
                ]
            );
        }
    }
}