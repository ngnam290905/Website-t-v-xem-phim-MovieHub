<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\DatVe;
use Illuminate\Support\Facades\DB;
use App\Models\VaiTro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\DiemThanhVien;
use App\Models\HangThanhVien;

class UserController extends Controller
{
    public function index(Request $request)
    {
<<<<<<< HEAD
        // Subqueries for aggregates
        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
            ->groupBy('id_dat_ve');
        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong,1)) as combo_total'))
            ->groupBy('id_dat_ve');

        $agg = DB::table('dat_ve as v')
            ->leftJoinSub($seatSub, 's', function($j){ $j->on('s.id_dat_ve','=','v.id'); })
            ->leftJoinSub($comboSub, 'c', function($j){ $j->on('c.id_dat_ve','=','v.id'); })
            ->select('v.id_nguoi_dung',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('MAX(v.created_at) as last_active'),
                DB::raw('SUM(COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)) as total_spent')
            )
            ->where('v.trang_thai', 1)
            ->groupBy('v.id_nguoi_dung');

        $users = NguoiDung::query()
            ->with(['vaiTro', 'diemThanhVien', 'hangThanhVien'])
            ->leftJoinSub($agg, 'a', function($j){ $j->on('a.id_nguoi_dung','=','nguoi_dung.id'); })
            ->whereNull('nguoi_dung.deleted_at')
            ->select('nguoi_dung.*',
                DB::raw('COALESCE(a.total_orders,0) as total_orders'),
                DB::raw('COALESCE(a.total_spent,0) as total_spent'),
                DB::raw('a.last_active')
            )
            ->orderByDesc('nguoi_dung.id')
            ->paginate(10);

        // Quick stats
        $totalUsers = (int) NguoiDung::whereNull('deleted_at')->count();
        $active30Days = (int) DB::table('dat_ve')
            ->where('trang_thai', 1)
            ->whereDate('created_at', '>=', now()->subDays(30)->toDateString())
            ->distinct('id_nguoi_dung')->count('id_nguoi_dung');
            
        // Count users by tier
        $tierDong = (int) DB::table('hang_thanh_vien')
            ->join('tier', 'hang_thanh_vien.id_tier', '=', 'tier.id')
            ->where('tier.ten_hang', 'Đồng')
            ->count();
            
        $tierBac = (int) DB::table('hang_thanh_vien')
            ->join('tier', 'hang_thanh_vien.id_tier', '=', 'tier.id')
            ->where('tier.ten_hang', 'Bạc')
            ->count();
            
        $tierVang = (int) DB::table('hang_thanh_vien')
            ->join('tier', 'hang_thanh_vien.id_tier', '=', 'tier.id')
            ->where('tier.ten_hang', 'Vàng')
            ->count();
            
        $tierKimCuong = (int) DB::table('hang_thanh_vien')
            ->join('tier', 'hang_thanh_vien.id_tier', '=', 'tier.id')
            ->where('tier.ten_hang', 'Kim cương')
            ->count();

        return view('admin.users.index', compact(
            'users', 'totalUsers', 'active30Days', 'tierDong', 'tierBac', 'tierVang', 'tierKimCuong'
        ));
=======
        $query = NguoiDung::with(['vaiTro', 'diemThanhVien', 'hangThanhVien'])
        ->whereNull('deleted_at');

        // 🔍 Nếu có tìm kiếm
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(10);

        return view('admin.users.index', compact('users'));
>>>>>>> origin/hoanganh
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

        // Hash mật khẩu
        $validated['mat_khau'] = Hash::make($validated['mat_khau']);

        // Đảm bảo trạng thái là 0 hoặc 1
        $validated['trang_thai'] = $request->has('trang_thai') ? 1 : 0;

        // Tạo user mới
        NguoiDung::create([
            'ho_ten' => $validated['ho_ten'],
            'email' => $validated['email'],
            'mat_khau' => $validated['mat_khau'],
            'sdt' => $validated['sdt'] ?? null,
            'dia_chi' => $validated['dia_chi'] ?? null,
            'id_vai_tro' => $validated['id_vai_tro'],
            'trang_thai' => $validated['trang_thai'],
        ]);

        // Redirect về trang danh sách người dùng với thông báo
        return redirect()->route('admin.users.index')->with('success', 'Tạo tài khoản thành công.');
    }

    public function show($id)
    {
        $user = NguoiDung::with('vaiTro')->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = NguoiDung::with(['diemThanhVien', 'hangThanhVien'])->findOrFail($id);
        $roles = VaiTro::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = NguoiDung::findOrFail($id);

        $validated = $request->validate([
            'ho_ten' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:100', Rule::unique('nguoi_dung', 'email')->ignore($user->id)],
            'mat_khau' => 'nullable|string|min:6|confirmed',
            'sdt' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string|max:255',
            'id_vai_tro' => 'required|exists:vai_tro,id',
            'trang_thai' => 'boolean',
            'tong_diem' => 'nullable|integer|min:0',
            'ten_hang' => 'nullable|string|max:50',
            'tong_chi_tieu' => 'nullable|numeric|min:0',
        ]);

        $data = $request->only([
            'ho_ten', 'email', 'sdt', 'dia_chi', 'id_vai_tro', 'trang_thai', 'tong_chi_tieu'
        ]);

        if ($request->filled('mat_khau')) {
            $data['mat_khau'] = Hash::make($request->mat_khau);
        }

        $data['trang_thai'] = $request->has('trang_thai') ? 1 : 0;

<<<<<<< HEAD
        // Cập nhật dữ liệu
        $user->update([
            'ho_ten' => $data['ho_ten'],
            'email' => $data['email'],
            'mat_khau' => $data['mat_khau'] ?? $user->mat_khau,
            'sdt' => $data['sdt'] ?? null,
            'dia_chi' => $data['dia_chi'] ?? null,
            'id_vai_tro' => $data['id_vai_tro'],
            'trang_thai' => $data['trang_thai'],
        ]);
=======
        // Cập nhật user
        $user->update($data);
>>>>>>> origin/hoanganh

        // Cập nhật điểm thành viên
        if ($request->filled('tong_diem')) {
            DiemThanhVien::updateOrCreate(
                ['id_nguoi_dung' => $user->id],
                ['tong_diem' => $request->tong_diem]
            );
        }

        // Cập nhật hạng thành viên
        if ($request->filled('ten_hang')) {
            HangThanhVien::updateOrCreate(
                ['id_nguoi_dung' => $user->id],
                ['ten_hang' => $request->ten_hang]
            );
        }

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật tài khoản thành công.');
    }

    public function destroy($id)
    {
        $user = NguoiDung::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Xóa tài khoản thành công.');
    }

    public function trash()
    {
        // Lấy danh sách user đã bị xóa mềm
        $users = NguoiDung::onlyTrashed()->with('vaiTro')->paginate(10);
        return view('admin.users.trash', compact('users'));
    }

    public function restore($id)
    {
        $user = NguoiDung::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.trash')->with('success', 'Khôi phục tài khoản thành công.');
    }

    public function show($id)
    {
        // Reuse aggregates for a single user
        $seatSub = DB::table('chi_tiet_dat_ve')
            ->select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
            ->groupBy('id_dat_ve');
        $comboSub = DB::table('chi_tiet_dat_ve_combo')
            ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong,1)) as combo_total'))
            ->groupBy('id_dat_ve');

        $agg = DB::table('dat_ve as v')
            ->leftJoinSub($seatSub, 's', function($j){ $j->on('s.id_dat_ve','=','v.id'); })
            ->leftJoinSub($comboSub, 'c', function($j){ $j->on('c.id_dat_ve','=','v.id'); })
            ->where('v.trang_thai', 1)
            ->where('v.id_nguoi_dung', $id)
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('MAX(v.created_at) as last_active'),
                DB::raw('SUM(COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)) as total_spent')
            )
            ->first();

        $user = NguoiDung::with(['vaiTro','diemThanhVien','hangThanhVien'])->findOrFail($id);
        
        // Add additional data to the user object
        $user->total_orders = (int)($agg->total_orders ?? 0);
        $user->total_spent = (float)($agg->total_spent ?? 0);
        $user->last_active = $agg->last_active ?? null;

        return view('admin.users.show', compact('user'));
    }
}