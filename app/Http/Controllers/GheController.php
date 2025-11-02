<?php

namespace App\Http\Controllers;

use App\Models\Ghe;
use App\Models\PhongChieu;
use App\Models\LoaiGhe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GheController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ghe::with(['phongChieu', 'loaiGhe']);

        if ($request->filled('id_phong')) {
            $query->where('id_phong', $request->id_phong);
        }

        $ghe = $query->orderBy('id_phong')
            ->orderBy('so_hang')
            ->orderBy('so_ghe')
            ->paginate(20)
            ->appends($request->query());

        // Quick stats
        $totalSeats = (int) Ghe::count();
        $activeSeats = (int) Ghe::where('trang_thai', 1)->count();
        $pausedSeats = (int) Ghe::where('trang_thai', 0)->count();
        $bookedToday = (int) DB::table('chi_tiet_dat_ve as c')
            ->join('dat_ve as d', 'd.id', '=', 'c.id_dat_ve')
            ->whereDate('d.created_at', now()->toDateString())
            ->where('d.trang_thai', '!=', 2)
            ->distinct('c.id_ghe')
            ->count('c.id_ghe');

        // Check if this is staff route
        if (request()->is('staff/*')) {
            return view('staff.ghe.index', compact('ghe', 'totalSeats', 'activeSeats', 'pausedSeats', 'bookedToday'));
        }

        $rooms = PhongChieu::orderBy('ten_phong')->get();
        $seatTypes = LoaiGhe::all();
        return view('admin.ghe.index', compact('ghe', 'rooms', 'seatTypes', 'totalSeats', 'activeSeats', 'pausedSeats', 'bookedToday'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $phongChieu = PhongChieu::where('trang_thai', 1)->get();
        $loaiGhe = LoaiGhe::all();
        if ($loaiGhe->isEmpty()) {
            LoaiGhe::insert([
                ['ten_loai' => 'Ghế thường', 'he_so_gia' => 1.00],
                ['ten_loai' => 'Ghế VIP',    'he_so_gia' => 1.50],
                ['ten_loai' => 'Ghế đôi',    'he_so_gia' => 2.00],
            ]);
            $loaiGhe = LoaiGhe::all();
        }
        
        return view('admin.ghe.create', compact('phongChieu', 'loaiGhe'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_phong' => 'required|exists:phong_chieu,id',
            'id_loai' => 'required|exists:loai_ghe,id',
            'so_ghe' => 'required|string|max:10',
            'so_hang' => 'required|integer|min:1',
            'trang_thai' => 'boolean'
        ]);

        // Check if seat already exists in the same room
        $existingGhe = Ghe::where('id_phong', $request->id_phong)
            ->where('so_ghe', $request->so_ghe)
            ->exists();

        if ($existingGhe) {
            return back()->withErrors(['so_ghe' => 'Ghế này đã tồn tại trong phòng.']);
        }

        Ghe::create($request->all());

        return redirect()->route('admin.ghe.index')
            ->with('success', 'Tạo ghế thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ghe $ghe)
    {
        $ghe->load(['phongChieu', 'loaiGhe']);
        
        // Check if this is staff route
        if (request()->is('staff/*')) {
            return view('staff.ghe.show', compact('ghe'));
        }
        
        return view('admin.ghe.show', compact('ghe'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ghe $ghe)
    {
        $phongChieu = PhongChieu::where('trang_thai', 1)->get();
        $loaiGhe = LoaiGhe::all();
        if ($loaiGhe->isEmpty()) {
            LoaiGhe::insert([
                ['ten_loai' => 'Ghế thường', 'he_so_gia' => 1.00],
                ['ten_loai' => 'Ghế VIP',    'he_so_gia' => 1.50],
                ['ten_loai' => 'Ghế đôi',    'he_so_gia' => 2.00],
            ]);
            $loaiGhe = LoaiGhe::all();
        }
        
        return view('admin.ghe.edit', compact('ghe', 'phongChieu', 'loaiGhe'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ghe $ghe)
    {
        $request->validate([
            'id_phong' => 'required|exists:phong_chieu,id',
            'id_loai' => 'required|exists:loai_ghe,id',
            'so_ghe' => 'required|string|max:10',
            'so_hang' => 'required|integer|min:1',
            'trang_thai' => 'boolean'
        ]);

        // Check if seat already exists in the same room (excluding current seat)
        $existingGhe = Ghe::where('id_phong', $request->id_phong)
            ->where('so_ghe', $request->so_ghe)
            ->where('id', '!=', $ghe->id)
            ->exists();

        if ($existingGhe) {
            return back()->withErrors(['so_ghe' => 'Ghế này đã tồn tại trong phòng.']);
        }

        $ghe->update($request->only([
            'id_phong',
            'id_loai',
            'so_ghe',
            'so_hang',
            'trang_thai'
        ]));

        return redirect()->route('admin.ghe.index')
            ->with('success', 'Cập nhật ghế thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ghe $ghe)
    {
        // Check if seat has any bookings
        if ($ghe->chiTietDatVe()->exists()) {
            return back()->withErrors(['error' => 'Không thể xóa ghế đã có vé đặt.']);
        }

        $ghe->delete();

        return redirect()->route('admin.ghe.index')
            ->with('success', 'Xóa ghế thành công!');
    }

    /**
     * Update status of seat (for staff)
     */
    public function updateStatus(Request $request, Ghe $ghe)
    {
        $request->validate([
            'trang_thai' => 'required|boolean'
        ]);

        $ghe->update(['trang_thai' => $request->trang_thai]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái ghế thành công!'
        ]);
    }

    /**
     * Bulk seat actions: lock/unlock/change type/delete
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:lock,unlock,type,delete',
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'integer|exists:ghe,id',
            'id_loai' => 'nullable|integer|exists:loai_ghe,id'
        ]);

        $action = $request->string('action');
        $ids = collect($request->seat_ids)->unique()->values();

        $seats = Ghe::whereIn('id', $ids)->get();
        if ($seats->isEmpty()) {
            return back()->with('error', 'Không tìm thấy ghế hợp lệ.');
        }

        $affected = 0; $skipped = [];
        switch ($action) {
            case 'lock':
                $affected = Ghe::whereIn('id', $seats->pluck('id'))->update(['trang_thai' => 0]);
                break;
            case 'unlock':
                $affected = Ghe::whereIn('id', $seats->pluck('id'))->update(['trang_thai' => 1]);
                break;
            case 'type':
                if (!$request->filled('id_loai')) {
                    return back()->with('error', 'Vui lòng chọn loại ghế khi đổi loại.');
                }
                $affected = Ghe::whereIn('id', $seats->pluck('id'))->update(['id_loai' => $request->id_loai]);
                break;
            case 'delete':
                foreach ($seats as $seat) {
                    if ($seat->bookingDetails()->exists()) {
                        $skipped[] = $seat->id;
                        continue;
                    }
                    $seat->delete();
                    $affected++;
                }
                break;
        }

        $msg = 'Thực hiện thành công. Ảnh hưởng: ' . $affected;
        if (!empty($skipped)) {
            $msg .= '. Bỏ qua ghế ID: ' . implode(',', $skipped);
        }

        return back()->with('success', $msg);
    }

    /**
     * Get seats by room
     */
    public function getByRoom(Request $request)
    {
        $request->validate([
            'id_phong' => 'required|exists:phong_chieu,id'
        ]);

        $ghe = Ghe::with(['loaiGhe'])
            ->where('id_phong', $request->id_phong)
            ->where('trang_thai', 1)
            ->orderBy('so_hang')
            ->orderBy('so_ghe')
            ->get();

        return response()->json($ghe);
    }

    /**
     * Generate seats for a room automatically
     */
    public function generateSeats(Request $request)
    {
        $request->validate([
            'id_phong' => 'required|exists:phong_chieu,id',
            'id_loai' => 'required|exists:loai_ghe,id'
        ]);

        $phongChieu = PhongChieu::findOrFail($request->id_phong);
        
        // Clear existing seats for this room
        Ghe::where('id_phong', $request->id_phong)->delete();

        // Generate seats
        for ($hang = 1; $hang <= $phongChieu->so_hang; $hang++) {
            for ($cot = 1; $cot <= $phongChieu->so_cot; $cot++) {
                Ghe::create([
                    'id_phong' => $request->id_phong,
                    'id_loai' => $request->id_loai,
                    'so_ghe' => chr(64 + $hang) . $cot, // A1, A2, ...
                    'so_hang' => $hang,
                    'trang_thai' => 1
                ]);
            }
        }

        return redirect()->route('admin.ghe.index')
            ->with('success', 'Tạo ghế tự động thành công!');
    }
}
